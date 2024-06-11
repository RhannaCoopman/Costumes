<x-app-layout>
    <div class="feed_grid">
        {{-- Post --}}
        <div class="feed_post">
            <a href="#" class="feed_post">
                <div class="post_badge">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-crown" width="24"
                        height="24" viewBox="0 0 24 24" stroke-width="1.5" stroke="#2c3e50" fill="none"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 6l4 6l5 -4l-2 10h-14l-2 -10l5 4z" />
                    </svg>
                    <p>Most liked today</p>
                </div>

                <div class="post_image">
                    <img src="https://i.pinimg.com/564x/53/07/78/530778a3f466a711fed82f1cf4cbfc09.jpg">

                    <like class="like" :post-id="12" :initial-liked="false" :initial-likes-count="4" />
                </div>
            </a>
        </div>

        <div class="feed_post">
            <a href="#" class="feed_post">
                <div class="post_badge">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-crown" width="24"
                        height="24" viewBox="0 0 24 24" stroke-width="1.5" stroke="#2c3e50" fill="none"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 6l4 6l5 -4l-2 10h-14l-2 -10l5 4z" />
                    </svg>
                    <p>Most liked today</p>
                </div>
                <div class="post_image">
                    <img src="https://i.pinimg.com/564x/7c/18/e3/7c18e3e985fcc7d15f8ab824b793b15f.jpg">

                    <like class="like" :post-id="12" :initial-liked="false" :initial-likes-count="4" />
                </div>
            </a>
        </div>

        <div class="feed_post">
            <a href="#" class="feed_post">
                <div class="post_image">
                    <img src="https://i.pinimg.com/736x/3a/c3/2b/3ac32b13ef336f8267e0cadfa7191826.jpg">

                    <like class="like" :post-id="12" :initial-liked="false" :initial-likes-count="4" />
                </div>
            </a>
        </div>

        <div class="feed_post">
            <a href="#" class="feed_post">
                <div class="post_badge">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-crown" width="24"
                        height="24" viewBox="0 0 24 24" stroke-width="1.5" stroke="#2c3e50" fill="none"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 6l4 6l5 -4l-2 10h-14l-2 -10l5 4z" />
                    </svg>
                    <p>Most liked today</p>
                </div>
                <div class="post_image">
                    <img src="https://i.pinimg.com/564x/53/07/78/530778a3f466a711fed82f1cf4cbfc09.jpg">

                    <like class="like" :post-id="12" :initial-liked="false" :initial-likes-count="4" />
                </div>
            </a>
        </div>
        <div class="feed_post">
            <a href="#" class="feed_post">
                <div class="post_image">
                    <img src="https://i.pinimg.com/564x/53/07/78/530778a3f466a711fed82f1cf4cbfc09.jpg">

                    <like class="like" :post-id="12" :initial-liked="false" :initial-likes-count="4" />
                </div>
            </a>
        </div>
    </div>
</x-app-layout>
