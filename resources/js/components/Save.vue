<template>
    <div>
        <button @click.stop.prevent="toggleSave" class="square_button detail_color">
            <img v-if="saved" :src="'/images/saved.svg'" />
            <img v-else :src="'/images/not_saved.svg'" />
        </button>
    </div>
</template>

<script>
    export default {
        name: "Save",
        props: {
            postId: {
                type: Number,
                required: true
            },
            initialSaved: {
                type: Boolean,
                required: true
            },
        },
        data() {
            return {
                saved: this.initialSaved,
            };
        },
        methods: {
            async toggleSave() {
                try {
                    const response = await axios.post(`/posts/${this.postId}/toggle-save`);
                    this.saved = response.data.saved;
                    this.savesCount = response.data.saves_count;
                } catch (error) {
                    console.error('Error toggling save:', error);
                }
            }
        }
    };
</script>
