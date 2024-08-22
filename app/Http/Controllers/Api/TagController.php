<?php

namespace App\Http\Controllers\Api;

use App\Enums\chatTypeEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Chat;
use App\Models\Message;
use App\Models\Tag;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;

class TagController extends Controller
{

    public function fetchSearchTags(Request $request)
    {
        $search = $request->input('search');

        $searchTags = Tag::where('name', 'LIKE', "%{$search}%")
            ->limit(20)
            ->get();

        return response()->json(['searchTags' => $searchTags]);
    }

    public function fetchRandomTags()
    {
        $randomTags = Tag::inRandomOrder()
            ->limit(10)
            ->get();

        return response()->json(['randomTags' => $randomTags]);
    }
}
