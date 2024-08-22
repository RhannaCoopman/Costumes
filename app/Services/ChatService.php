<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\User;

class ChatService
{
    public function getIndividualChatBetweenUsers($userId1, $userId2)
    {
        // Retrieve the users
        $user1 = User::find($userId1);
        $user2 = User::find($userId2);

        if (!$user1 || !$user2) {
            return null;
        }

        // Get the chat IDs for both users
        $user1ChatIds = $user1->chats()->pluck('chats.id')->toArray();
        $user2ChatIds = $user2->chats()->pluck('chats.id')->toArray();

        // Find common chat IDs
        $commonChatIds = array_intersect($user1ChatIds, $user2ChatIds);

        if (empty($commonChatIds)) {
            return null;
        }

        // Find an individual chat from the common chat IDs
        $individualChat = Chat::individual()
                            ->whereIn('id', $commonChatIds)
                            ->first();

        if ($individualChat) {
            return [
                'id' => $individualChat->id,
                'uuid' => $individualChat->uuid
            ];
        }

        return null;
    }
}
