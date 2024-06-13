<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupTag;
use App\Models\GroupUser;
use App\Models\User;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function recommendGroups()
    {
        $user_id = auth()->id();
        $user = User::with('tags')->find($user_id);
        $user_tags = $user->tags->pluck('id')->toArray();
        // $user_location = $user->location; // Assume location is a field in the User model

        // Step 1: Find existing groups matching user tags and location
        $matchingGroups = Group::whereHas('tags', function ($query) use ($user_tags) {
            $query->whereIn('tag_id', $user_tags);
        })
            // ->where('location', $user_location) // Assuming 'location' is a field in the Group model
            ->withCount('users')
            ->orderBy('users_count', 'desc')
            ->get();

        // Step 2: If no matching groups, create a new group
        if ($matchingGroups->isEmpty()) {
            $newGroup = Group::create([
                'name' => $user->name . "'s Group", // Customize as needed
                // 'location' => $user_location,
            ]);

            // Attach user tags to the new group
            foreach ($user_tags as $tag_id) {
                GroupTag::create([
                    'group_id' => $newGroup->id,
                    'tag_id' => $tag_id,
                ]);
            }

            // Add the user to the new group
            GroupUser::create([
                'group_id' => $newGroup->id,
                'user_id' => $user_id,
            ]);

            $matchingGroups->push($newGroup); // Add the new group to the results
        }

        return view('groups.show', [
            'groups' => $matchingGroups,
        ]);
    }
}
