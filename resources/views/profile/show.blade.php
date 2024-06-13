<x-app-layout>
    <h1>Hi {{ $user->name }}</h1>

    {{-- Saved items --}}
    <div class="image-container">
        <div class="image-navigation">
            <div>
                <img src="/images/saved.svg" />
                <p>Saved items</p>
            </div>
            <div>
                <p>See all</p>
                <img src="/images/arrow-right.svg" />
            </div>
        </div>
        <div class="scroll-container">
            <div class="grid">
                @foreach ($user->savedPosts as $post)
                    <a class="item" href="{{ route('post.detail', ['post' => $post->uuid]) }}">
                        <img src="{{ Storage::disk('public')->url($post->firstImage->path) }}" />
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Uploaded items --}}
    <div class="image-container">
        <div class="image-navigation">
            <div>
                <img src="/images/saved.svg" />
                <p>Your uploads</p>
            </div>
            <div>
                <p>See all</p>
                <img src="/images/arrow-right.svg" />
            </div>
        </div>
        <div class="scroll-container">
            <div class="grid">
                @foreach ($user->uploadedPosts as $post)
                    <a class="item" href="{{ route('post.detail', ['post' => $post->uuid]) }}">
                        <img src="{{ Storage::disk('public')->url($post->firstImage->path) }}" />
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
