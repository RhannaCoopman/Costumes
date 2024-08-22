<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class RecommendationService
{
    /**
     * Get the user's tags with scores.
     *
     * @param int $userId
     * @return array
     */
    public static function getUserTagsWithScores($userId)
    {
        $user = User::with('tagsWithScore')->find($userId);
        $tagsWithScores = [];

        foreach ($user->tagsWithScore as $tag) {
            $tagsWithScores[$tag->id] = $tag->pivot->score;
        }

        return $tagsWithScores;
    }

    /**
     * Calculate the weighted Jaccard similarity coefficient between user tags with scores and post tags.
     *
     * @param array $userTags
     * @param array $postTags
     * @return float
     */
    public static function weightedJaccardSimilarity($userTags, $postTags)
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
}
