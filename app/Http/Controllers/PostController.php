<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Annotation;
use App\Models\Like;
use App\Models\Post;
use App\Models\Save;
use App\Models\Tag;
use App\Models\User;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class PostController extends Controller
{
    public function feed(): View
    {
        $user_id = auth()->id();

        // Join post tags with user tags and include the score
        $commonTags = DB::table('post_tags as pt')->join('user_tags as ut', 'pt.tag_id', '=', 'ut.tag_id')->select('pt.post_id', DB::raw('SUM(ut.score) as total_tag_score'))->where('ut.user_id', $user_id)->groupBy('pt.post_id');

        $posts = DB::table('posts as p')
            ->leftJoinSub($commonTags, 'ct', 'p.id', '=', 'ct.post_id')
            ->leftJoin('likes as l', function ($join) use ($user_id) {
                $join->on('p.id', '=', 'l.post_id')->where('l.user_id', '=', $user_id);
            })
            ->leftJoin('saves as s', function ($join) use ($user_id) {
                $join->on('p.id', '=', 's.post_id')->where('s.user_id', '=', $user_id);
            })
            ->leftJoin(DB::raw('(SELECT post_id, COUNT(*) as likes_count FROM likes GROUP BY post_id) as lc'), 'p.id', '=', 'lc.post_id')
            ->leftJoin(DB::raw('(SELECT post_id, COUNT(*) as saves_count FROM saves GROUP BY post_id) as sc'), 'p.id', '=', 'sc.post_id')
            ->leftJoin(DB::raw('(SELECT post_id, MIN(id) as first_image_id FROM images GROUP BY post_id) as fi'), 'p.id', '=', 'fi.post_id')
            ->leftJoin('images as i', 'fi.first_image_id', '=', 'i.id')
            ->select(
                'p.id',
                'p.content',
                'p.uuid',
                'p.popular',
                'p.created_at',
                'p.updated_at',
                DB::raw('COALESCE(ct.total_tag_score, 0) as total_tag_score'),
                DB::raw('COALESCE(lc.likes_count, 0) as likes_count'),
                DB::raw('COALESCE(sc.saves_count, 0) as saves_count'),
                DB::raw('CASE WHEN l.id IS NOT NULL THEN "true" ELSE "false" END as liked'),
                DB::raw('CASE WHEN s.id IS NOT NULL THEN "true" ELSE "false" END as saved'),
                'i.path as first_image_path',
                DB::raw('
                    (
                        0.1 * COALESCE(ct.total_tag_score, 0) +
                        0.2 * COALESCE(lc.likes_count, 0) +
                        0.2 * COALESCE(sc.saves_count, 0) +
                        0.2 * DATEDIFF(NOW(), p.created_at) +
                        0.1 * DATEDIFF(NOW(), p.updated_at) +
                        4 * IF(s.id IS NOT NULL, 0.7, 1) +
                        4 * IF(l.id IS NOT NULL, 0.7, 1)
                    ) as score
                '),
            )
            ->orderByDesc('score')
            ->get();

        return view('posts.feed', [
            'posts' => $posts,
        ]);
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

    public function create(): View
    {
        return view('posts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        try {
            DB::beginTransaction();

            $post = Post::create([
                'uuid' => Uuid::uuid4()->toString(),
                'content' => $request->content,
                'user_id' => auth()->id() ?? 1,
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
                    ->tags()
                    ->where('tag_id', $tag->id)
                    ->exists()
            ) {
                // Update existing pivot table record
                $currentScore = $user
                    ->tags()
                    ->where('tag_id', $tag->id)
                    ->first()->pivot->score;
                $newScore = max(0, $currentScore + $weight); // Ensure the score does not go below zero

                if ($newScore == 0) {
                    // Detach the tag if the new score is zero
                    $user->tags()->detach($tag->id);
                } else {
                    // Update the pivot table with the new score and update timestamps
                    $user->tags()->updateExistingPivot($tag->id, [
                        'score' => $newScore,
                        'updated_at' => now(),
                    ]);
                }
            } elseif ($weight > 0) {
                // Attach new record if liking and record does not exist with timestamps
                $user->tags()->attach($tag->id, [
                    'score' => $weight,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
