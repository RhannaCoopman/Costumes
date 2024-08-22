<template>
    <div v-if="screen === 'upload'" class="container">
        <!-- Screen navigation -->
        <div class="screen_navigation">
            <div>
                <a href="/" class="round_button faded_white_color">
                    <img :src="'/images/arrow-left.svg'" />
                </a>
                <h1 v-if="this.multiple">Upload one or multiple pictures</h1>
                <h1 v-else="this.multiple">Upload your picture</h1>
            </div>

            <div
                v-if="files.length > 0 && this.multiple"
                @click="changeScreen('annotations')"
            >
                <p>Next</p>
                <img :src="'/images/arrow-right.svg'" />
            </div>
        </div>

        <!-- Upload area -->
        <div class="upload-container">
            <label for="file_upload" class="label">
                <img :src="'/images/upload.svg'" />
                <p>Tap to browse</p>
            </label>
            <input
                id="file_upload"
                type="file"
                :multiple="this.multiple"
                @change="handleFiles"
                accept="image/*"
                class="hidden"
            />
        </div>

        <!-- Drag-element -->
        <draggable
            v-if="this.multiple"
            v-model="files"
            group="people"
            @start="isDragging = true"
            @end="isDragging = false"
            item-key="id"
            class="upload-grid"
        >
            <template #item="{ element }">
                <div class="grid-item">
                    <img :src="element.preview" alt="" />
                </div>
            </template>
        </draggable>
    </div>

    <div v-if="screen === 'annotations'">
        <!-- Screen navigation -->
        <div class="screen_navigation">
            <div>
                <div class="round_button faded_white_color">
                    <img
                        @click="changeScreen('upload')"
                        :src="'/images/arrow-left.svg'"
                    />
                </div>
                <h1>Annotations</h1>
            </div>

            <div @click="changeScreen('text')">
                <p>Next</p>
                <img :src="'/images/arrow-right.svg'" />
            </div>
        </div>

        <!-- Instructions -->
        <p>tap the picture to add or edit an annotation</p>

        <!-- Image and annotations -->
        <div class="annotations_container">
            <!-- Image -->
            <div class="image">
                <!-- Image -->
                <img
                    :src="files[currentImage].preview"
                    alt=""
                    id="image"
                    @click="showAnnotationsPopup($event)"
                />

                <!-- Navigation -->
                <div v-if="this.multiple" class="image_navigation">
                    <button @click="prevImage" :disabled="currentImage === 0">
                        Previous
                    </button>

                    <div class="indicator">
                        <span
                            v-for="(file, index) in files"
                            :key="index"
                            :class="{ active: index === currentImage }"
                        ></span>
                    </div>

                    <button
                        @click="nextImage"
                        :disabled="currentImage === files.length - 1"
                    >
                        Next
                    </button>
                </div>

                <!-- Existing annotations -->
                <div
                    v-for="(annotation, index) in files[currentImage]
                        .annotations"
                    :key="index"
                    class="annotation"
                    @click="showAnnotation(index)"
                    :style="{
                        top: annotation.y + '%',
                        left: annotation.x + '%',
                    }"
                >
                    <div
                        class="outer-circle"
                        :class="{ active: index === currentAnnotation }"
                    >
                        <div class="inner-circle">
                            <p>{{ index + 1 }}</p>
                        </div>
                    </div>
                </div>

                <!-- New annotation -->
                <div
                    v-if="showPopup"
                    class="annotation new"
                    :style="{
                        top: popupStyle.top + '%',
                        left: popupStyle.left + '%',
                    }"
                >
                    <div class="outer-circle new">
                        <div class="inner-circle">
                            <p></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Annotations -->
            <div class="annotations">
                <div class="index_column">
                    <!-- Annotation bubbles with number -->
                    <div
                        v-for="(annotation, index) in files[currentImage]
                            .annotations"
                        :key="index"
                        class="annotation_container"
                        :class="{ active: index === currentAnnotation }"
                        @click="showAnnotation(index)"
                    >
                        <div
                            class="annotation"
                            :class="{ active: index === currentAnnotation }"
                        >
                            <div
                                class="outer-circle"
                                :class="{ active: index === currentAnnotation }"
                            >
                                <div class="inner-circle">
                                    <img
                                        v-if="index === currentAnnotation"
                                        :src="'/images/liked.svg'"
                                    />

                                    <p v-else>{{ index + 1 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inactive annotation  -->
                    <div
                        v-if="
                            files[currentImage].annotations.length === 0 ||
                            showPopup
                        "
                        class="annotation_container"
                        :class="{ active: showPopup }"
                    >
                        <div class="annotation">
                            <div
                                class="outer-circle"
                                :class="{
                                    inactive: !showPopup,
                                    active: showPopup,
                                }"
                            >
                                <div class="inner-circle">
                                    <p></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="wide-column"
                    :class="{
                        inactive:
                            files[currentImage].annotations.length < 1 &&
                            !showPopup,
                    }"
                >
                    <p v-if="files[currentImage].annotations.length < 1">
                        Tap the picture to add or edit an annotation
                    </p>
                    <div v-else>
                        <div v-if="currentAnnotation === null">
                            <p>Tap an annotation to edit</p>
                        </div>
                    </div>

                    <div v-if="showPopup">
                        <div>
                            <div class="flex_column">
                                <div class="input">
                                    <label>Url</label>
                                    <input
                                        class="light"
                                        type="text"
                                        v-model="annotation.url"
                                        autofocus
                                        @keyup.enter="scrape()"
                                        @input="resetState()"
                                    />
                                </div>

                                <button
                                    v-if="
                                        !this.annotation.loading &&
                                        !this.annotation.success &&
                                        !this.annotation.error
                                    "
                                    class="detail_color"
                                    @click="scrape()"
                                >
                                    Zoek kledingsstuk
                                </button>

                                <div
                                    v-if="this.annotation.loading"
                                    class="message"
                                >
                                    <span class="loading-icon"></span>
                                    <p>Loading</p>
                                </div>

                                <div
                                    v-if="this.annotation.success"
                                    class="message success-message"
                                >
                                    <img :src="'/images/check.svg'" />
                                    <p>
                                        Successfully extracted
                                        {{
                                            this.annotation.extractedFields.join(
                                                ", "
                                            )
                                        }}
                                    </p>
                                </div>

                                <div
                                    v-if="this.annotation.error"
                                    class="message error-message"
                                >
                                    <img :src="'/images/cross.svg'" />
                                    <p>
                                        Error:
                                        {{ this.annotation.errorMessage }}
                                    </p>
                                </div>
                            </div>

                            <hr />

                            <div class="input">
                                <label>Name</label>
                                <input
                                    class="light"
                                    type="text"
                                    v-model="annotation.name"
                                    autofocus
                                    @keyup.enter="saveAnnotation"
                                />
                            </div>

                            <div class="input">
                                <label>Description</label>
                                <input
                                    class="light"
                                    type="text"
                                    v-model="annotation.description"
                                    autofocus
                                    @keyup.enter="saveAnnotation"
                                />
                            </div>

                            <div class="input">
                                <label>Brand</label>
                                <input
                                    class="light"
                                    type="text"
                                    v-model="annotation.brand"
                                    autofocus
                                    @keyup.enter="saveAnnotation"
                                />
                            </div>

                            <div class="input">
                                <label>Store</label>
                                <input
                                    class="light"
                                    type="text"
                                    v-model="annotation.store"
                                    autofocus
                                    @keyup.enter="saveAnnotation"
                                />
                            </div>

                            <div class="input">
                                <label>Article number</label>
                                <input
                                    class="light"
                                    type="text"
                                    v-model="annotation.article_number"
                                    autofocus
                                    @keyup.enter="saveAnnotation"
                                />
                            </div>

                            <div class="input">
                                <label>Price</label>
                                <input
                                    class="light"
                                    type="text"
                                    v-model="annotation.price"
                                    autofocus
                                    @keyup.enter="saveAnnotation"
                                />
                            </div>

                            <div v-if="this.annotation.image" class="image">
                                <img :src="annotation.image" />
                            </div>
                        </div>

                        <button
                            v-if="!this.annotation.loading"
                            @click="saveAnnotation"
                            class="detail_color"
                        >
                            Save
                        </button>
                    </div>

                    <div v-if="currentAnnotation != null && !showPopup">
                        <div class="flex_column">
                            <button
                            class="faded_main_color"
                                @click="removeAnnotation(currentAnnotation)"
                            >
                                Delete annotation
                            </button>
                            <hr />
                        </div>

                        <div>
                            <div class="input">
                                <label>Url</label>
                                <input
                                    class="light"
                                    type="text"
                                    v-model="
                                        files[currentImage].annotations[
                                            currentAnnotation
                                        ].url
                                    "
                                />
                            </div>

                            <hr />

                            <div class="input">
                                <label>Name</label>
                                <input
                                    class="light"
                                    type="text"
                                    v-model="
                                        files[currentImage].annotations[
                                            currentAnnotation
                                        ].name
                                    "
                                    autofocus
                                />
                            </div>

                            <div class="input">
                                <label>Description</label>
                                <input
                                    class="light"
                                    type="text"
                                    v-model="
                                        files[currentImage].annotations[
                                            currentAnnotation
                                        ].description
                                    "
                                    autofocus
                                />
                            </div>

                            <div class="input">
                                <label>Brand</label>
                                <input
                                    class="light"
                                    type="text"
                                    v-model="
                                        files[currentImage].annotations[
                                            currentAnnotation
                                        ].brand
                                    "
                                />
                            </div>

                            <div class="input">
                                <label>Store</label>
                                <input
                                    class="light"
                                    type="text"
                                    v-model="
                                        files[currentImage].annotations[
                                            currentAnnotation
                                        ].store
                                    "
                                />
                            </div>

                            <div class="input">
                                <label>Article number</label>
                                <input
                                    class="light"
                                    type="text"
                                    v-model="
                                        files[currentImage].annotations[
                                            currentAnnotation
                                        ].article_number
                                    "
                                />
                            </div>

                            <div class="input">
                                <label>Price</label>
                                <input
                                    class="light"
                                    type="text"
                                    v-model="
                                        files[currentImage].annotations[
                                            currentAnnotation
                                        ].price
                                    "
                                />
                            </div>

                            <div class="image">
                                <img
                                    v-if="
                                        files[currentImage].annotations[
                                            currentAnnotation
                                        ].image
                                    "
                                    :src="
                                        files[currentImage].annotations[
                                            currentAnnotation
                                        ].image
                                    "
                                />
                            </div>
                        </div>

                        <div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div v-if="screen === 'text'">
        <!-- Screen navigation -->
        <div class="screen_navigation">
            <div>
                <div class="round_button faded_white_color">
                    <img
                        @click="changeScreen('annotations')"
                        :src="'/images/arrow-left.svg'"
                    />
                </div>
                <h1>Description</h1>
            </div>

            <div @click="savePost">
                <p>Save</p>
                <img :src="'/images/arrow-right.svg'" />
            </div>
        </div>

        <!-- Input fields -->
        <div class="rounded_corners_container white_color flex_column">
            <div class="input">
                <label>Content</label>
                <input class="light" type="text" v-model="this.content" />
            </div>

            <div class="input">
                <label>Tags</label>
                <input
                    class="light"
                    type="text"
                    v-model="this.tags"
                    placeholder="Enter tags, seperated with a comma"
                />
            </div>
        </div>
    </div>
</template>

<script>
import draggable from "vuedraggable";
import axios from "axios";

export default {
    data() {
        return {
            files: [],
            isDragging: false,
            screen: "upload",
            currentImage: 0,
            currentAnnotation: null,
            showPopup: false,
            annotation: {
                name: String,
                description: String,
                brand: String,
                store: String,
                article_number: String,
                price: String,
                url: String,
                image: String,
                loading: false,
                success: false,
                error: false,
                errorMessage: "",
                extractedFields: [],
            },
            popupStyle: {
                top: 0,
                left: 0,
            },
            content: "",
            tags: "",
        };
    },
    props: {
        multiple: Boolean,
    },
    components: {
        draggable,
    },
    methods: {
        async scrape() {
            this.resetState();
            this.annotation.loading = true;

            axios
                .get(`/api/scrape-webshop`, {
                    params: {
                        url: this.annotation.url,
                    },
                })
                .then((response) => {
                    this.annotation.name = response.data.name;
                    this.annotation.description = response.data.description;
                    this.annotation.brand = response.data.brand;
                    this.annotation.article_number =
                        response.data.article_number;
                    this.annotation.price = response.data.price;
                    this.annotation.image = response.data.image;
                    this.annotation.store = response.data.store;

                    this.annotation.extractedFields = Object.keys(
                        this.annotation
                    ).filter(
                        (key) =>
                            this.annotation[key] &&
                            key !== "url" &&
                            key !== "loading" &&
                            key !== "success" &&
                            key !== "error" &&
                            key !== "errorMessage" &&
                            key !== "extractedFields"
                    );
                    this.annotation.loading = false;
                    this.annotation.success = true;
                })

                .catch((error) => {
                    this.annotation.loading = false;
                    this.annotation.url = "";
                    this.annotation.error = true;
                    this.annotation.errorMessage =
                        error.response?.data?.message || "An error occurred";
                    console.error("Error fetching data:", error);
                });
        },
        resetState() {
            this.annotation.loading = false;
            this.annotation.success = false;
            this.annotation.error = false;
            this.annotation.errorMessage = "";
            this.annotation.extractedFields = [];
        },
        // async scrape() {
        //     this.resetState();
        //     this.loading = true;
        //     try {
        //         const response = await axios.post(`/api/scrape-webshop`, {
        //             url: this.annotation.url
        //         });
        //         const data = response.data;

        //         this.annotation.name = data.name || '';
        //         this.annotation.description = data.description || '';
        //         this.annotation.brand = data.brand || '';
        //         this.annotation.store = data.store || '';
        //         this.annotation.article_number = data.article_number || '';
        //         this.annotation.price = data.price || '';
        //         this.annotation.image = data.image || '';

        //         // Determine which fields were successfully extracted
        //         this.extractedFields = Object.keys(this.annotation).filter(key => this.annotation[key] &&
        //             key !== 'url');

        //         this.loading = false;
        //         this.success = true;
        //     } catch (error) {
        //         this.loading = false;
        //         this.error = true;
        //         this.errorMessage = error.response?.data?.message || 'An error occurred';
        //     }
        // },
        showAnnotationsPopup(event) {
            this.currentAnnotation = null;
            this.showPopup = true;
            const imageRect = event.target.getBoundingClientRect();
            const offsetX = event.clientX - imageRect.left;
            const offsetY = event.clientY - imageRect.top;
            const xPercent = parseFloat(
                ((offsetX / imageRect.width) * 100).toFixed(2)
            );
            const yPercent = parseFloat(
                ((offsetY / imageRect.height) * 100).toFixed(2)
            );

            this.popupStyle = {
                top: yPercent - 2,
                left: xPercent - 2,
            };
            this.annotation = {};
        },
        showAnnotation(index) {
            this.currentAnnotation = index;
        },
        removeAnnotation(index) {
            this.files[this.currentImage].annotations.splice(index, 1);
            // Ensure currentAnnotation is reset if it was pointing to the removed annotation
            if (
                this.currentAnnotation === index ||
                this.currentAnnotation >=
                    this.files[this.currentImage].annotations.length
            ) {
                this.currentAnnotation = null;
            }
            // If no annotations left, reset popup visibility and input
            if (this.files[this.currentImage].annotations.length === 0) {
                this.showPopup = false;
                this.annotation = {};
            }
        },
        saveAnnotation() {
            this.showPopup = false;
            const annotationObject = {
                x: this.popupStyle.left,
                y: this.popupStyle.top,
                name: this.annotation.name,
                brand: this.annotation.brand,
                store: this.annotation.store,
                article_number: this.annotation.article_number,
                price: this.annotation.price,
                url: this.annotation.url,
                image: this.annotation.image,
            };

            if (!this.files[this.currentImage].annotations) {
                this.$set(this.files[this.currentImage], "annotations", [
                    annotationObject,
                ]);
            } else {
                this.files[this.currentImage].annotations.push(
                    annotationObject
                );
            }
            this.currentAnnotation =
                this.files[this.currentImage].annotations.length - 1;
        },
        handleFiles(event) {
            const files = event.target.files;

            Array.from(files).forEach((file) => {
                this.files.push({
                    file, // Retain the original file
                    preview: URL.createObjectURL(file),
                    annotations: [], // Initialize annotations array for each file
                });
            });

            if (!this.multiple) {
                this.screen = "annotations";
            }
        },
        changeScreen(screen) {
            this.screen = screen;
        },
        async savePost() {
            const formData = new FormData();
            formData.append("content", this.content);
            formData.append("tags", this.tags);

            this.files.forEach((item, index) => {
                formData.append(`images[${index}]`, item.file);
                item.annotations.forEach((annotation, annotationIndex) => {
                    formData.append(
                        `images[${index}][annotations][${annotationIndex}][x]`,
                        annotation.x
                    );
                    formData.append(
                        `images[${index}][annotations][${annotationIndex}][y]`,
                        annotation.y
                    );
                    formData.append(
                        `images[${index}][annotations][${annotationIndex}][name]`,
                        annotation.name
                    );
                    formData.append(
                        `images[${index}][annotations][${annotationIndex}][store]`,
                        annotation.store
                    );
                    formData.append(
                        `images[${index}][annotations][${annotationIndex}][url]`,
                        annotation.url
                    );
                });
            });

            try {
                const response = await axios.post("/api/save-post", formData, {
                    headers: {
                        "Content-Type": "multipart/form-data",
                    },
                });
                window.location.href = "/profile";
            } catch (error) {
                if (error.response && error.response.data) {
                    console.error(
                        "Validation errors:",
                        error.response.data.errors
                    );
                }
                console.error("Error uploading images:", error);
            }
        },
        prevImage() {
            if (this.currentImage > 0) {
                this.currentImage--;
                this.currentAnnotation = null;
            }
        },
        nextImage() {
            if (this.currentImage < this.files.length - 1) {
                this.currentImage++;
                this.currentAnnotation = null;
            }
        },
    },
};
</script>
