<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Interest;
use App\Models\Post;
use App\Models\User;
use App\Services\ChatService;
use App\Services\RecommendationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Geocoder\Provider\GoogleMaps\GoogleMaps;
use Geocoder\Query\GeocodeQuery;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use Geocoder\StatefulGeocoder;
use Illuminate\Support\Facades\DB;

class CommunityController extends Controller
{
    public function interestsAndUserInterests()
    {
        $user_id = Auth::id();
        $user = User::findOrFail($user_id);
        $allInterests = Interest::all();
        $userInterests = $user->interests()->pluck('interests.id');

        return response()->json([
            'allInterests' => $allInterests,
            'userInterests' => $userInterests,
            'city' => $user->city,
            'zip_code' => $user->zip_code,
        ]);
    }
    // function getUserTagsWithScores($user_id) {
    //     $user = User::find($user_id);
    //     return $user->tagsWithScore->pluck('pivot.score', 'tag_id')->toArray();
    // }

    // function weightedJaccardSimilarity($set1, $set2) {
    //     $intersection = 0;
    //     $union = 0;

    //     foreach ($set1 as $tag_id => $score1) {
    //         if (isset($set2[$tag_id])) {
    //             $score2 = $set2[$tag_id];
    //             $intersection += min($score1, $score2);
    //             $union += max($score1, $score2);
    //         } else {
    //             $union += $score1;
    //         }
    //     }

    //     foreach ($set2 as $tag_id => $score2) {
    //         if (!isset($set1[$tag_id])) {
    //             $union += $score2;
    //         }
    //     }

    //     return $union == 0 ? 0 : $intersection / $union;
    // }

    // function getUsers($top_n = 5) {
    //     // Initialize ChatService
    //     $chatService = new ChatService();

    //     $target_user_id = Auth::id();

    //     // Get tags and scores of the target user
    //     $target_user_tags = $this->getUserTagsWithScores($target_user_id);

    //     // Get all users except the target user
    //     $users = User::where('id', '!=', $target_user_id)->get();

    //     // Array to store similarity scores
    //     $similarity_scores = [];

    //     foreach ($users as $user) {
    //         $user_tags = $this->getUserTagsWithScores($user->id);
    //         $similarity = $this->weightedJaccardSimilarity($target_user_tags, $user_tags);
    //         $similarity_scores[$user->id] = $similarity;
    //     }

    //     // Sort users by similarity score in descending order
    //     arsort($similarity_scores);

    //     // Get top N users
    //     $recommended_user_ids = array_slice(array_keys($similarity_scores), 0, $top_n, true);

    //     // Get recommended users with chat ID
    //     $recommended_users = User::whereIn('id', $recommended_user_ids)->get()->map(function ($user) use ($target_user_id, $chatService) {
    //         $chat = $chatService->getIndividualChatBetweenUsers($target_user_id, $user->id);
    //         $user->chat_uuid = $chat ? $chat['uuid'] : null;
    //         return $user;
    //     });

    //     return response()->json([
    //         'data' => $recommended_users,
    //     ]);
    // }

    // /**
    //  * Get the user's tags with scores.
    //  *
    //  * @param int $userId
    //  * @return array
    //  */
    // private function getUserTagsWithScores($userId)
    // {
    //     $user = User::with('tagsWithScore')->find($userId);
    //     $tagsWithScores = [];

    //     foreach ($user->tagsWithScore as $tag) {
    //         $tagsWithScores[$tag->id] = $tag->pivot->score;
    //     }

    //     info($tagsWithScores);
    //     return $tagsWithScores;
    // }

    /**
     * Calculate the weighted Jaccard similarity coefficient between user tags with scores and post tags.
     *
     * @param array $userTags
     * @param array $postTags
     * @return float
     */
    // private function weightedJaccardSimilarity($userTags, $postTags)
    // {
    //     $intersection = 0;
    //     $union = array_sum($userTags);

    //     foreach ($postTags as $tag) {
    //         if (isset($userTags[$tag])) {
    //             $intersection += $userTags[$tag];
    //         } else {
    //             $union += 1; // Assuming each post tag has a default weight of 1
    //         }
    //     }

    //     if ($union == 0) {
    //         return 0;
    //     }

    //     info($intersection);

    //     return $intersection / $union;
    // }


    // function getUsers(Request $request)
    // {
    //     $interests = $request->input('interests', []);

    //     info($interests);

    //     $target_user_id = Auth::id();

    //     // Get tags and scores of the target user
    //     $target_user_tags = RecommendationService::getUserTagsWithScores($target_user_id);

    //     // Get all users except the target user and filter by interests if provided
    //     $usersQuery = User::where('id', '!=', $target_user_id);

    //     if (!empty($interests)) {
    //         $usersQuery->whereHas('interests', function ($query) use ($interests) {
    //             $query->whereIn('interests.id', $interests);
    //         });
    //     }

    //     $users = $usersQuery->get();

    //     // Array to store recommended users
    //     $recommendations = [];

    //     foreach ($users as $user) {
    //         $current_user_tags = RecommendationService::getUserTagsWithScores($user->id);

    //         $similarity = RecommendationService::weightedJaccardSimilarity($target_user_tags, $current_user_tags);

    //         if ($similarity > 0) {
    //             // Find the chat between the target user and the current user
    //             $chat = Chat::findChatBetweenUsers($target_user_id, $user->id);

    //             // Add user and chat information to the recommendations
    //             $recommendations[] = [
    //                 'user' => $user,
    //                 'score' => $similarity,
    //                 'chat_uuid' => $chat ? $chat->uuid : null, // Include chat UUID or null if no chat exists
    //             ];
    //         }
    //     }

    //     usort($recommendations, function ($a, $b) {
    //         return $b['score'] <=> $a['score'];
    //     });

    //     // Get top N users
    //     $recommendedUsers = array_slice($recommendations, 0, 5);

    //     return response()->json([
    //         'data' => $recommendedUsers,
    //     ]);
    // }


    // function getUsers(Request $request)
    // {
    //     $interests = $request->input('interests', []);
    //     $city = $request->input('city');
    //     info($city);
    //     $zipcode = $request->input('zipcode');
    //     $distance = $request->input('distance', 50);
    //     info($zipcode);

    //     $everywhere = $request->input('everywhere', true);
    //     info($everywhere);

    //     $target_user_id = Auth::id();

    //     // Get tags and scores of the target user
    //     $target_user_tags = RecommendationService::getUserTagsWithScores($target_user_id);

    //     // Get all users except the target user and filter by interests if provided
    //     $usersQuery = User::where('id', '!=', $target_user_id);

    //     if (!empty($interests)) {
    //         $usersQuery->whereHas('interests', function ($query) use ($interests) {
    //             $query->whereIn('interests.id', $interests);
    //         });
    //     }

    //     if ($everywhere) {
    //         info('location');
    //         $httpClient = new \Http\Adapter\Guzzle6\Client();
    //         $provider = new \Geocoder\Provider\GoogleMaps\GoogleMaps($httpClient, null, 'YOUR_GOOGLE_MAPS_API_KEY');
    //         $geocoder = new \Geocoder\StatefulGeocoder($provider, 'en');

    //         try {
    //             $geocodeQuery = GeocodeQuery::create("{$city}, {$zipcode}");
    //             $geocodeResult = $geocoder->geocodeQuery($geocodeQuery);

    //             if ($geocodeResult->isEmpty()) {
    //                 return response()->json(['error' => 'Location not found'], 404);
    //             }

    //             $location = $geocodeResult->first();
    //             $latitudeA = $location->getCoordinates()->getLatitude();
    //             $longitudeA = $location->getCoordinates()->getLongitude();

    //             $usersQuery->select(DB::raw("*,
    //             (6371 * acos(cos(radians($latitudeA))
    //             * cos(radians(latitude))
    //             * cos(radians(longitude) - radians($longitudeA))
    //             + sin(radians($latitudeA))
    //             * sin(radians(latitude)))) AS distance"))
    //                 ->having("distance", "<", $distance)
    //                 ->orderBy("distance");
    //         } catch (Exception $e) {
    //             return response()->json(['error' => 'Geocoding failed: ' . $e->getMessage()], 500);
    //         }
    //     }

    //     $users = $usersQuery->get();

    //     // Array to store recommended users
    //     $recommendations = [];

    //     foreach ($users as $user) {
    //         $current_user_tags = RecommendationService::getUserTagsWithScores($user->id);

    //         $similarity = RecommendationService::weightedJaccardSimilarity($target_user_tags, $current_user_tags);

    //         if ($similarity > 0) {
    //             // Find the chat between the target user and the current user
    //             $chat = Chat::findChatBetweenUsers($target_user_id, $user->id);

    //             // Add user and chat information to the recommendations
    //             $recommendations[] = [
    //                 'user' => $user,
    //                 'score' => $similarity,
    //                 'chat_uuid' => $chat ? $chat->uuid : null, // Include chat UUID or null if no chat exists
    //             ];
    //         }
    //     }

    //     usort($recommendations, function ($a, $b) {
    //         return $b['score'] <=> $a['score'];
    //     });

    //     // Get top N users
    //     $recommendedUsers = array_slice($recommendations, 0, 5);

    //     return response()->json([
    //         'data' => $recommendedUsers,
    //     ]);
    // }


    function getUsers(Request $request)
    {
        $interests = $request->input('interests', []);
        $zipcode = $request->input('zipcode');
        $distance = $request->input('distance', 50);
        $everywhere = $request->input('everywhere');

        $target_user_id = Auth::id();

        // Get tags and scores of the target user
        $target_user_tags = RecommendationService::getUserTagsWithScores($target_user_id);

        // Query users based on interests
        $usersQuery = User::where('id', '!=', $target_user_id);

        if (!empty($interests)) {
            $usersQuery->whereHas('interests', function ($query) use ($interests) {
                $query->whereIn('interests.id', $interests);
            });
        }

        // If searching by zipcode, apply location filters
        if ($everywhere != "true") {
            $zipcode = intval($zipcode);

            // Filter users by zip code range (within +/- $distance)
            $usersQuery->where(function ($query) use ($zipcode, $distance) {
                $query->whereBetween('zip_code', [$zipcode - $distance, $zipcode + $distance]);
            });

            // Get users based on the filtered query
            $users = $usersQuery->get();

            // Array to store recommended users
            $recommendations = [];

            foreach ($users as $user) {
                $current_user_tags = RecommendationService::getUserTagsWithScores($user->id);

                $similarity = RecommendationService::weightedJaccardSimilarity($target_user_tags, $current_user_tags);

                if ($similarity > 0) {
                    // Find the chat between the target user and the current user
                    $chat = Chat::findChatBetweenUsers($target_user_id, $user->id);

                    // Add user and chat information to the recommendations
                    $recommendations[] = [
                        'user' => $user,
                        'score' => $similarity,
                        'chat_uuid' => $chat ? $chat->uuid : null, // Include chat UUID or null if no chat exists
                    ];
                }
            }

            // Sort recommendations by score descending
            usort($recommendations, function ($a, $b) {
                return $b['score'] <=> $a['score'];
            });

            // Get top 5 recommended users
            $recommendedUsers = array_slice($recommendations, 0, 5);
        } else {
            // If no zipcode provided, fetch all users based on interests only
            $users = $usersQuery->get();

            // Array to store recommended users
            $recommendations = [];

            foreach ($users as $user) {
                $current_user_tags = RecommendationService::getUserTagsWithScores($user->id);

                $similarity = RecommendationService::weightedJaccardSimilarity($target_user_tags, $current_user_tags);

                if ($similarity > 0) {
                    // Find the chat between the target user and the current user
                    $chat = Chat::findChatBetweenUsers($target_user_id, $user->id);

                    // Add user and chat information to the recommendations
                    $recommendations[] = [
                        'user' => $user,
                        'score' => $similarity,
                        'chat_uuid' => $chat ? $chat->uuid : null, // Include chat UUID or null if no chat exists
                    ];
                }
            }

            // Sort recommendations by score descending
            usort($recommendations, function ($a, $b) {
                return $b['score'] <=> $a['score'];
            });

            // Get top 5 recommended users
            $recommendedUsers = array_slice($recommendations, 0, 5);
        }

        return response()->json([
            'data' => $recommendedUsers,
        ]);
    }

}
