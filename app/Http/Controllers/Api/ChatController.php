<?php

namespace App\Http\Controllers\Api;

use App\Enums\chatTypeEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;

class ChatController extends Controller
{
    public function store(Request $request, Chat $chat)
    {
        $request->validate([
            'text' => 'required|string',
        ]);

        $message = new Message([
            'uuid' => Uuid::uuid4()->toString(),
            'chat_id' => $chat->id,
            'sender_id' => Auth::id(),
            'message' => $request->text,
        ]);

        $message->save();
        $message->sender = Auth::user();

        info(json_encode($message, JSON_PRETTY_PRINT));

        return response()->json($message, 201);
    }

    public function fetchChat($chatUuid, Request $request)
    {
        info($request->page);
        info('fetch');

        // Fetch the chat by UUID
        $chat = Chat::where('uuid', $chatUuid)->firstOrFail();

        // Paginate the messages for the fetched chat, ordered by creation date from newest to oldest
        $messages = $chat->messages()
                        ->with('sender')
                        ->orderBy('id', 'asc')
                        ->paginate(10, ['*'], 'page', $request->page);

        // Prepare the response data
        $response = [
            'chat' => $chat,
            'messages' => $messages
        ];

        return response()->json($response);
    }

    public function createChat(Request $request)
    {
        $chat = Chat::create(['type' => chatTypeEnum::INDIVIDUAL]);
        $chat->users()->attach([Auth::id(), $request->user_id]);

        info('New chat created:');
        info($chat);

        return response()->json([
            'uuid' => $chat->uuid,
        ]);
    }
}
