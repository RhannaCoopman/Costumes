<x-app-layout>
    <h1>Get inspired</h1>

    <div class="feed_grid">

        @foreach ($posts as $post)
            <div class="feed_post">
                <a href="{{ route('post.detail', ['post' => $post->uuid]) }}" class="feed_post">
                    @if ($post->popular)
                        <div class="post_badge">
                            <img :src="'/images/crown.svg'" />
                            <p class="badge_text">Most liked today</p>
                        </div>
                    @endif

                    <div class="post_image">
                        @if ($post->first_image_path)
                            <img src="{{ Storage::disk('public')->url($post->first_image_path) }}" alt="Post Image">
                        @endif

                        <div class="actions">
                            <save class="save" :post-id="{{ $post->id }}"
                                :initial-saved="{{ json_encode($post->saved === 'true' ? true : false) }}"></save>

                            <like class="like" :post-id="{{ $post->id }}"
                                :initial-liked="{{ json_encode($post->liked === 'true' ? true : false) }}"
                                :initial-likes-count="{{ $post->likes_count }}"></like>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</x-app-layout>
