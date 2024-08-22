<template>
    <div class="flex_column">
        <div class="flex_column">
            <div class="flex">
                <input type="text" class="dark" v-model="search" @input="fetchSearchTags" />
                <img src="../../../../public/images/search.svg" />

                <div v-if="chosenTag" class="flex rounded_corners_container white_color">
                    <p v-if="this.chosenTag" :key="chosenTag.uuid" class="">#{{ chosenTag.name }}</p>
                    <p  @click="deleteChosenTag">X</p>
                </div>

            </div>

            <div v-if="search && searchTags.length" class="flex_column dropdown">
                <p v-for="tag in searchTags" @click="onTagClick(tag)" :key="tag.uuid" class="rounded_corners_container white_color">{{ tag.name }}</p>
            </div>
        </div>


        <div class="scroll-container">
            <div v-if="this.randomTags" class="grid">
                <p v-for="tag in this.randomTags" @click="onTagClick(tag)" :key="tag.uuid" class="rounded_corners_container detail_color"> #{{
                    tag.name }}</p>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Filters',
    data() {
        return {
            search: '',
            isLoading: true,
            randomTags: [],
            searchTags: [],
            chosenTag: null,
        };
    },
    methods: {
        onTagClick(tag) {
            this.chosenTag = tag;
            this.$emit('tag-click', this.chosenTag);
        },
        deleteChosenTag() {
            this.chosenTag = null;
            this.$emit('tag-click', this.chosenTag);
        },
        async fetchRandomTags() {
            try {
                const response = await axios.get("/api/tags/random");

                this.randomTags = response.data.randomTags;

            } catch (error) {
                console.error("Error fetching tags:", error);
            } finally {
                this.isLoading = false;
            }
        },
        async fetchSearchTags() {
            try {
                const response = await axios.get("/api/tags/search", {
                    params: { search: this.search },
                });

                this.searchTags = response.data.searchTags;
                console.log(this.searchTags);

            } catch (error) {
                console.error("Error fetching search tags:", error);
            } finally {
                this.isLoading = false;
            }
        },
    },
    mounted() {
        this.fetchRandomTags();
    },
};
</script>

<style scoped>
.dropdown {
    z-index: 10;
    width: 200px;
    max-height: 200px;
    overflow-y: auto;
}

.dropdown_item {
    padding: 10px;
    cursor: pointer;
}

.dropdown_item:hover {
    background-color: #f0f0f0;
}
</style>
