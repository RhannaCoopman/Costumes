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

    public function searchChat(Request $request, User $user)
    {
        info('search chat');
        $userId1 = 3;
        $userId2 = $user->id;

        $chat = $this->chatService->getIndividualChatBetweenUsers($userId1, $userId2);
        info('chat:');
        info($chat);


        if (!$chat) {
            info('geen chat:');

            // Create a new chat if it doesn't exist
            $chat = Chat::create(['type' => chatTypeEnum::INDIVIDUAL]);
            $chat->users()->attach([$userId1, $userId2]);

            info('chat:');
            info($chat);

            return response()->json(['chat_uuid' => $chat['uuid']]);

        }

        return response()->json(['chat_uuid' => $chat['uuid']]);
    }

    public function openChat(Chat $chat)
    {
        if (!$chat) {
            info('geen chat:');

            // Create a new chat if it doesn't exist
            // $chat = Chat::create(['type' => chatTypeEnum::INDIVIDUAL]);
            // $chat->users()->attach([Auth::id(), 2]);

            // info('chat:');
            // info($chat);

        }

        $chat = $chat->load('users');

        // Get the logged-in user's ID
        $loggedInUserId = auth()->id();

        // Find the other user in the chat
        $otherUser = $chat->users->firstWhere('id', '!=', $loggedInUserId);

        return view('chats.detail', [
            'chat' => $chat,
            'otherUserName' => $otherUser ? $otherUser->name : 'Unknown User'
        ]);
    }

    public function list(): View
    {
        $user = User::where('id', Auth::id())->first();
        $chats = $user->chats()->with('latestMessage')->get();

        info(json_encode($chats, JSON_PRETTY_PRINT));

        return view('chats.list', [
            'chats' => $chats,
        ]);
    }
}
