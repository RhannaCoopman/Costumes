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

    /**
     * Calculate the weighted Jaccard similarity coefficient between user tags with scores and post tags.
     *
     * @param array $userTags
     * @param array $postTags
     * @return float
     */

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
