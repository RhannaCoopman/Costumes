<x-app-layout>
    <div class="chat-container">
        <h1>Chats</h1>
        <div class="search"></div>

        <div class="messages">
            @foreach($chats as $chat)
                <a href="{{ route('chats.detail', [$chat->uuid]) }}" class="flex big_gap rounded_corners_container white_color">
                    <div class="small_round_container">
                        {{-- Assuming there is a profile image URL in the sender object --}}
                        {{-- <img src="{{ $chat->latest_message->sender->profile_image_url ?? '/images/default-profile.png' }}" alt="Profile Image" /> --}}
                    </div>
                    <div class="flex_column">
                        <p class="heavy_font">{{ $chat->latestMessage->sender->id === auth()->user()->id ? 'You' : $chat->latestMessage->sender->name }}</p>
                        <div class="flex">
                            <p>{{ $chat->latestMessage->message }}</p>
                            <img src="/images/liked.svg" />
                            <p class="light_font">{{ \Carbon\Carbon::parse($chat->latestMessage->created_at)->format('H:i') }}</p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</x-app-layout>
