<template>
    <div class="annotations_container">
        <div class="image">

            <!-- Screen navigation -->
            <div class="screen_navigation">
                <div>
                    <a href="/" class="round_button faded_white_color">
                        <img :src="'/images/arrow-left.svg'" />
                    </a>
                </div>
            </div>

            <!-- Image -->
            <div class="post_image">
                <img :src="getImageUrl(this.post.images[this.imageIndex].path)" :alt="'Image for post ' + post.id" />
                <div class="actions">
                    <like class="round_button faded_white_color" :post-id="this.post.id" :initial-liked="this.liked"
                        :initial-likes-count="this.likesCount"></like>

                    <save class="round_button faded_white_color" :post-id="this.post.id" :initial-saved="this.saved"></save>
                </div>

            </div>
            <!-- Navigation -->
            <div v-if="this.post.images.length > 1" class="navigation">
                <button @click="prevImage" :disabled="imageIndex === 0">
                    Previous
                </button>

                <div class="indicator">
                    <span v-for="(file, index) in this.post.images" :key="index"
                        :class="{ active: index === imageIndex }"></span>
                </div>

                <button @click="nextImage" :disabled="imageIndex === this.post.images.length - 1">
                    Next
                </button>
            </div>

            <!-- Annotations -->
            <div v-for="(annotation, index) in this.post.images[imageIndex].annotations" :key="index"
                class="annotation" @click="showAnnotation(index)"
                :style="{
                    top: (annotation.yPosition - 2) + '%',
                    left: (annotation.xPosition - 2) + '%',
                }">
                <div class="outer-circle" :class="{ active: index === currentAnnotation }">
                    <div class="inner-circle">
                        <p>{{ index + 1 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="annotations">
            <div class="index_column">
                <!-- Annotation bubbles with number -->
                <div v-for="(annotation, index) in this.post.images[imageIndex].annotations" :key="index"
                    class="annotation_container" :class="{ active: index === currentAnnotation }"
                    @click="showAnnotation(index)">
                    <div class="annotation" :class="{ active: index === currentAnnotation }">
                        <div class="outer-circle" :class="{ active: index === currentAnnotation }">
                            <div class="inner-circle">
                                <img v-if="index === currentAnnotation" :src="'/images/liked.svg'" />

                                <p v-else>{{ index + 1 }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="wide-column">
                <div v-if="currentAnnotation != null">
                    <p>{{ this . post . images[imageIndex] . annotations[currentAnnotation] . name }}</p>
                    <button @click="goToLink(this.post.images[imageIndex].annotations[currentAnnotation].url)">Visit
                        webshop</button>

                </div>
                <div v-else>
                    <p>{{ this . post . user . name }}</p>
                    <p>{{ this . post . formatted_created_at }}</p>
                    <p>{{ this . post . content }}</p>
                </div>
            </div>
        </div>
    </div>

    <feed-grid :search="false" :post-id="this.post.id"></feed-grid>

</template>

<script>
    export default {
        data() {
            return {
                imageIndex: 0,
                currentAnnotation: null,
            };
        },
        props: {
            post: Object,
            liked: Boolean,
            likesCount: Number,
            saved: Boolean,
        },
        methods: {
            getImageUrl(path) {
                return `/storage/${path}`;
            },
            prevImage() {
                if (this.imageIndex > 0) {
                    this.imageIndex--;
                    this.currentAnnotation = null;
                }
            },
            nextImage() {
                if (this.imageIndex < this.post.images.length - 1) {
                    this.imageIndex++;
                    this.currentAnnotation = null;
                }
            },
            showAnnotation(index) {
                this.currentAnnotation = index;
            },
            goToLink(link) {
                window.open(link, '_blank');
            },
        }
    };
</script>
