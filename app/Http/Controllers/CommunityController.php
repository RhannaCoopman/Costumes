<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CommunityController extends Controller
{
    public function feed(): View
    {
        return view('community.feed');
    }
}
