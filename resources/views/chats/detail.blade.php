<x-app-layout>
    <div class="chat-container">
        <div class="screen_navigation">
            <div>
                <a href="/chats" class="round_button faded_white_color">
                    <img :src="'/images/arrow-left.svg'" />
                </a>
                <h1>{{ $otherUserName }}</h1>
            </div>

            <div>
                <p>Next</p>
            </div>
        </div>

        <chat-messages
            :chat-uuid="'{{ $chat->uuid }}'"
            :logged-in-user-id="{{ auth()->user()->id }}"
            >
        </chat-messages>
    </div>
</x-app-layout>
