<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class TagService
{
    public static function calculateTagScoreJoin($user_id)
    {
        return DB::table('post_tags as pt')
            ->join('user_tags as ut', 'pt.tag_id', '=', 'ut.tag_id')
            ->select('pt.post_id', DB::raw('SUM(ut.score) as total_tag_score'))
            ->where('ut.user_id', $user_id)
            ->groupBy('pt.post_id');
    }
}
