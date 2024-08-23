<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Annotation;
use App\Models\Interest;
use App\Models\Like;
use App\Models\Post;
use App\Models\Save;
use App\Models\Tag;
use App\Models\User;
use App\Services\RecommendationService;
use App\Services\TagService;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class PostController extends Controller
{
    public function fetchPosts(Request $request)
    {
        $user_id = Auth::id();
        $user = Auth::user();

        // Get tags associated with user
        $userTags = RecommendationService::getUserTagsWithScores($user_id);

        // Parameters
        $perPage = $request->input('per_page', 10);
        $tag = $request->input('tag', null);
        $postId = $request->input('post_id', null);

        // Query
        $query = Post::with('tags', 'firstImage');

        if ($postId) {
            // Get tags of the given post
            $post = Post::findOrFail($postId);
            $postTags = $post->tags->pluck('name')->toArray();

            // Fetch posts with similar tags
            $query->whereHas('tags', function ($q) use ($postTags) {
                $q->whereIn('name', $postTags);
            });
        } else if ($tag) {
            // Assuming $tag is an array, get the tag name
            $tagName = is_array($tag) && isset($tag['name']) ? $tag['name'] : null;

            if ($tagName) {
                $query->whereHas('tags', function ($q) use ($tagName) {
                    $q->where('name', $tagName);
                });
            }
        }

        $posts = $query->paginate($perPage);

        // Adding is_liked and is_saved properties to each post
        $posts->each(function ($post) use ($user_id) {
            $post->is_liked = $post->isLikedByUser($user_id);
            $post->is_saved = $post->isSavedByUser($user_id);
        });

        $recommendations = [];

        foreach ($posts as $post) {
            $postTags = $post->tags->pluck('id')->toArray();
            $similarity = $this->weightedJaccardSimilarity($userTags, $postTags);

            if ($similarity > 0) {
                $recommendations[] = [
                    'post' => $post,
                    'score' => $similarity
                ];
            }
        }

        // Sort recommendations by similarity score in descending order
        usort($recommendations, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        // Randomize a bit by shuffling the top N recommendations
        $topN = $request->input('top_n', 20);
        $recommendedPosts = array_slice($recommendations, 0, $topN);

        // Shuffle a small portion of the top recommendations to add randomness
        $shuffleCount = min(count($recommendedPosts), 5); // Adjust the number to control randomness
        $postsToShuffle = array_slice($recommendedPosts, 0, $shuffleCount);
        shuffle($postsToShuffle);

        // Merge shuffled posts back into the recommendations
        $recommendedPosts = array_merge($postsToShuffle, array_slice($recommendedPosts, $shuffleCount));

        return response()->json([
            'data' => $recommendedPosts,
            'current_page' => $posts->currentPage(),
            'last_page' => $posts->lastPage(),
        ]);
    }

    public function fetchWelcomePosts()
    {
        $posts = Post::where('user_id', 3)->with('firstImage')->get();
        $allInterests = Interest::all();

        info(json_encode($posts, JSON_PRETTY_PRINT));

        return response()->json([
            'data' => $posts,
            'allInterests' => $allInterests,
        ]);
    }

    public function saveWelcomeData(Request $request)
    {
        $user = Auth::user();

        // Log user ID and selected interests
        info($user->id);
        info($request->interests);

        // Save the user's selected interests
        $interests = $request->interests;
        $user->interests()->sync($interests);  // Sync the selected interests with the user

        // Mark the welcome flow as completed
        $user->welcome_flow_completed = true;
        $user->save();

        return response()->json(['message' => 'Welcome flow completed and interests saved']);
    }


    /**
     * Calculate the weighted Jaccard similarity coefficient between user tags with scores and post tags.
     *
     * @param array $userTags
     * @param array $postTags
     * @return float
     */
    private function weightedJaccardSimilarity($userTags, $postTags)
    {
        $intersection = 0;
        $union = array_sum($userTags);

        foreach ($postTags as $tag) {
            if (isset($userTags[$tag])) {
                $intersection += $userTags[$tag];
            } else {
                $union += 1; // Assuming each post tag has a default weight of 1
            }
        }

        if ($union == 0) {
            return 0;
        }

        return $intersection / $union;
    }

    public function post(Post $post): View
    {
        $user_id = Auth::id();

        $post->load('images.annotations', 'user');

        $tags = $post->tags;
        $this->updateUserTagScores($user_id, $tags, 'post');

        $liked = $post->isLikedByUser($user_id);
        $likesCount = $post->likesCount();
        $saved = $post->isSavedByUser($user_id);

        return view('posts.post', [
            'post' => $post,
            'liked' => $liked,
            'likesCount' => $likesCount,
            'saved' => $saved,
        ]);
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $post = Post::create([
                'uuid' => Uuid::uuid4()->toString(),
                'content' => $request->content,
                'user_id' => Auth::id(),
            ]);

            $tags = explode(',', $request->tags);

            foreach ($tags as $tagName) {
                $tag = Tag::firstOrCreate(['name' => trim($tagName)], ['uuid' => Uuid::uuid4()->toString()]);

                // Attach the tag to the post
                $post->tags()->attach($tag->id);
            }

            $images = $request->file('images');

            foreach ($images as $index => $file) {
                // Retrieve tags data for the current image
                $annotations = $request->input("images.$index.annotations");

                // Save the image
                $path = $file->store('uploads', 'public');
                $imageModel = new Image();
                $imageModel->filename = $file->getClientOriginalName();
                $imageModel->mime = $file->getClientMimeType();
                $imageModel->path = $path;
                $imageModel->size = $file->getSize();
                $imageModel->post_id = $post->id;
                $imageModel->save();

                if ($annotations) {
                    // Save tags associated with the image
                    foreach ($annotations as $annotation) {
                        $data = new Annotation();
                        $data->uuid = Uuid::uuid4()->toString();
                        $data->xPosition = $annotation['x'];
                        $data->yPosition = $annotation['y'];
                        $data->user_description = $annotation['name'];
                        $data->name = $annotation['name'];
                        $data->shop = $annotation['store'];
                        $data->url = $annotation['url'];
                        $data->image_id = $imageModel->id;
                        $data->save();
                    }
                }
            }

            DB::commit();

            return redirect()->route('post.detail', ['post' => $post]);
        } catch (Exception $e) {
            DB::rollback();

            Log::error($e->getMessage());

            return response()->json(['error' => 'Post creation failed.'], 500);
        }
    }

    public function toggleLike($post_id)
    {
        $user_id = auth()->id();
        $like = Like::where('post_id', $post_id)->where('user_id', $user_id)->first();

        // Fetch the post and its tags
        $post = Post::with('tags')->find($post_id);
        $tags = $post->tags;

        if ($like) {
            // Unlike: Remove like and decrease tag scores
            $like->delete();
            $this->updateUserTagScores($user_id, $tags, 'unlike');
            return response()->json(['liked' => false, 'likes_count' => $post->likes()->count()]);
        } else {
            // Like: Add like and increase tag scores
            Like::create(['user_id' => $user_id, 'post_id' => $post_id]);
            $this->updateUserTagScores($user_id, $tags, 'like');
            return response()->json(['liked' => true, 'likes_count' => $post->likes()->count()]);
        }
    }

    public function toggleSave($post_id)
    {
        $user_id = auth()->id();
        $save = Save::where('post_id', $post_id)->where('user_id', $user_id)->first();

        // Fetch the post and its tags
        $post = Post::with('tags')->find($post_id);
        $tags = $post->tags;

        if ($save) {
            // Unsave: Remove save and decrease tag scores
            $save->delete();
            $this->updateUserTagScores($user_id, $tags, 'unsave');
            return response()->json(['saved' => false, 'saves_count' => $post->saves()->count()]);
        } else {
            // Save: Add save and increase tag scores
            Save::create(['user_id' => $user_id, 'post_id' => $post_id]);
            $this->updateUserTagScores($user_id, $tags, 'save');
            return response()->json(['saved' => true, 'saves_count' => $post->saves()->count()]);
        }
    }

    private function updateUserTagScores($user_id, $tags, $action)
    {
        // Define action weights
        $action_weights = [
            'like' => 3,
            'unlike' => -3,
            'save' => 4,
            'unsave' => -4,
            'post' => 1,
        ];

        // Get the weight for the given action
        $weight = $action_weights[$action];

        $user = User::find($user_id);

        foreach ($tags as $tag) {
            if (
                $user
                ->tagsWithScore()
                ->where('tag_id', $tag->id)
                ->exists()
            ) {
                // Update existing pivot table record
                $currentScore = $user
                    ->tagsWithScore()
                    ->where('tag_id', $tag->id)
                    ->first()->pivot->score;
                $newScore = max(0, $currentScore + $weight); // Ensure the score does not go below zero

                if ($newScore == 0) {
                    // Detach the tag if the new score is zero
                    $user->tagsWithScore()->detach($tag->id);
                } else {
                    // Update the pivot table with the new score and update timestamps
                    $user->tagsWithScore()->updateExistingPivot($tag->id, [
                        'score' => $newScore,
                        'updated_at' => now(),
                    ]);
                }
            } elseif ($weight > 0) {
                // Attach new record if liking and record does not exist with timestamps
                $user->tagsWithScore()->attach($tag->id, [
                    'score' => $weight,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
