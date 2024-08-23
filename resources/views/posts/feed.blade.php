<x-app-layout>
    <div class="chat-container">
        <h1>Get inspired</h1>

        @if($welcome_flow_completed)
            <feed-grid :search="true"></feed-grid>
        @else
            <p>Welcome! Please complete the welcome flow to personalize your feed.</p>
            <welcome-flow></welcome-flow>
        @endif
    </div>
</x-app-layout>
