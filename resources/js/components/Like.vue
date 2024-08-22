<template>
    <div>
        <button @click.stop.prevent="toggleLike" class="square_button detail_color">
            <img v-if="liked" :src="'/images/liked.svg'" />
            <img v-else :src="'/images/not_liked.svg'" />
        </button>
    </div>
</template>

<script>
export default {
    name: "Like",
    props: {
        postId: {
            type: Number,
            required: true
        },
        initialLiked: {
            type: Boolean,
            required: true
        },
    },
    data() {
        return {
            liked: this.initialLiked,
            likesCount: this.initialLikesCount
        };
    },
    methods: {
        async toggleLike() {
            try {
                const response = await axios.post(`/posts/${this.postId}/toggle-like`);
                this.liked = response.data.liked;
                this.likesCount = response.data.likes_count;
            } catch (error) {
                console.error('Error toggling like:', error);
            }
        }
    }
};
</script>
