<?php

namespace App\Http\Controllers;

use App\Enums\chatTypeEnum;
use Illuminate\Http\Request;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;

class ChatController extends Controller
{
    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    public function openChat(Chat $chat)
    {
        $chat = $chat->load('users');

        // Get the logged-in user's ID
        $loggedInUserId = auth()->id();

        // Find the other user in the chat
        $otherUser = $chat->users->firstWhere('id', '!=', $loggedInUserId);

        return view('chats.detail', [
            'chat' => $chat,
            'otherUser' => $otherUser ? $otherUser : 'Unknown User'
        ]);
    }

    // public function list(): View
    // {
    //     $user = User::where('id', Auth::id())->first();
    //     $chats = $user->chats()->with('latestMessage')->get();

    //     return view('chats.list', [
    //         'chats' => $chats,
    //     ]);
    // }

    public function list(): View
{
    // Get the authenticated user
    $user = Auth::user();

    // Get all chats of the authenticated user with the latest message and users
    $chats = $user->chats()->with(['latestMessage', 'users'])->get();

    // Map over the chats to attach the recipient information
    $chats->each(function ($chat) use ($user) {
        // Find the other user (recipient) in the chat
        $chat->recipient = $chat->users->where('id', '!=', $user->id)->first();
    });

    return view('chats.list', [
        'chats' => $chats,
    ]);
}
}
