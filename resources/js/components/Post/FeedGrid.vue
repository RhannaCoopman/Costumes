<template>
    <feed-filters v-if="search" @tag-click="handleTagClick"></feed-filters>

    <div class="feed_grid" @scroll="handleScroll">
        <div v-for="post in posts" :key="post.post.id" class="feed_post">
            <a :href="`/post/${post.post.uuid}`" class="feed_post">
                <div v-if="post.post.is_popular" class="post_badge">
                    <img src="../../../../public/images/crown.svg" />
                    <p class="badge_text">Most liked today</p>
                </div>

                <div class="post_image">
                    <img
                        v-if="post.post.first_image.path"
                        :src="getImageUrl(post.post.first_image.path)"
                        alt="Post Image"
                    />

                    <div class="actions">
                        <save
                            class="save"
                            :post-id="post.post.id"
                            :initial-saved="post.post.is_saved"
                        ></save>
                        <like
                            class="like"
                            :post-id="post.post.id"
                            :initial-liked="post.post.is_liked"
                        ></like>
                    </div>
                </div>
            </a>
        </div>

        <div v-if="this.isLoading" class="loading"></div>
        <p v-if="!this.hasMorePosts">There are no more posts left.</p>
    </div>

</template>

<script>
import axios from "axios";

export default {
    data() {
        return {
            posts: [],
            page: 1,
            hasMorePosts: true,
            isLoading: false,
            selectedTag: null,
        };
    },
    props: {
        search: {
            type: Boolean,
            required: false,
        },
        postId: { // Add this prop
            type: Number,
            required: false,
        },
    },

    methods: {
        async fetchPosts(page = 1, perPage = 20) {
            if (this.isLoading) return;

            this.isLoading = true;

            try {
                const response = await axios.get("/api/posts", {
                    params: { page, per_page: perPage, tag: this.selectedTag, post_id: this.postId },
                });

                console.log(response.data);

                const fetchedPosts = response.data.data;

                if (fetchedPosts.length === 0) {
                    this.hasMorePosts = false;
                } else {
                    this.posts = [...this.posts, ...fetchedPosts];
                    this.page++;
                }
            } catch (error) {
                console.error("Error fetching data:", error);
            } finally {
                this.isLoading = false;
            }
        },
        handleScroll(event) {
            const { scrollTop, scrollHeight, clientHeight } = event.target;

            if (scrollTop + clientHeight >= scrollHeight - 500) {
                if (this.hasMorePosts) {
                    this.fetchPosts(this.page);
                }
            }
        },
        getImageUrl(path) {
            return `/storage/${path}`;
        },
        handleTagClick(tag) {
            this.selectedTag = tag;
            console.log(this.selectedTag);
            this.posts = [];

            this.page = 1; // Reset the page number when a new tag is selected
            this.hasMorePosts = true; // Reset the flag to allow further scrolling
            this.fetchPosts(1, 20); // Fetch posts based on the selected tag
        },
    },
    mounted() {
        this.fetchPosts();
    },
};
</script>
