<template>
    <div v-if="screen === 'upload'" class="container">
        <h1 v-if="this.multiple">Upload multiple pictures</h1>
        <h1 v-else="this.multiple">Upload your picture</h1>

        <div class="upload-container">
            <label for="file_upload" class="label">
                <img :src="'/images/upload.svg'" />

                <p>Tap to browse</p>
            </label>
            <input id="file_upload" type="file" :multiple="this.multiple" @change="handleFiles" accept="image/*" class="hidden" />
        </div>

        <draggable v-if="this.multiple" v-model="files" group="people" @start="isDragging = true"
            @end="isDragging = false" item-key="id" class="upload-grid">
            <template #item="{ element }">
                <div class="grid-item">
                    <img :src="element.preview" alt="" />
                </div>
            </template>
        </draggable>

        <button v-if="files.length > 0" @click="changeScreen('annotations')">
            Volgende
        </button>
    </div>

    <div v-if="screen === 'annotations'">
        <h1>Annotations</h1>
        <p>tap the picture to add or edit an annotation</p>

        <div class="annotations_container">
            <div class="image">

                <!-- Image -->
                <img :src="files[currentImage].preview" alt="" id="image"
                    @click="showAnnotationsPopup($event)" />

                <!-- Navigation -->
                <div v-if="this.multiple" class="navigation">
                    <button @click="prevImage" :disabled="currentImage === 0">
                        Previous
                    </button>

                    <div class="indicator">
                        <span v-for="(file, index) in files" :key="index"
                            :class="{ active: index === currentImage }"></span>
                    </div>

                    <button @click="nextImage" :disabled="currentImage === files.length - 1">
                        Next
                    </button>
                </div>

                <!-- Existing annotations -->
                <div v-for="(annotation, index) in files[currentImage].annotations" :key="index"
                    class="annotation" @click="showAnnotation(index)"
                    :style="{
                        top: (annotation.y - 2) + '%',
                        left: (annotation.x - 2) + '%',
                    }">
                    <div class="outer-circle" :class="{ active: index === currentAnnotation }">
                        <div class="inner-circle">
                            <p>{{ index + 1 }}</p>
                        </div>
                    </div>
                </div>

                <!-- New annotation -->
                <div v-if="showPopup" class="annotation new"
                    :style="{
                        top: popupStyle.top + '%',
                        left: popupStyle.left + '%',
                    }">
                    <div class="outer-circle new">
                        <div class="inner-circle">
                            <p></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="annotations">
                <div class="index_column">
                    <!-- Annotation bubbles with number -->
                    <div v-for="(annotation, index) in files[currentImage].annotations" :key="index"
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

                    <!-- Inactive annotation  -->
                    <div v-if="files[currentImage].annotations.length === 0 || showPopup" class="annotation_container"
                        :class="{ active: showPopup }">
                        <div class="annotation">
                            <div class="outer-circle" :class="{ inactive: !showPopup, active: showPopup }">
                                <div class="inner-circle">
                                    <p></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wide-column"
                    :class="{ inactive: files[currentImage].annotations.length < 1 && !showPopup }">
                    <p v-if="files[currentImage].annotations.length < 1">Tap the picture to add or edit an annotation
                    </p>
                    <div v-else>
                        <div v-if="currentAnnotation === null">
                            <p>Tap an annotation to edit</p>
                        </div>
                    </div>

                    <div v-if="showPopup">
                        <input type="text" v-model="annotation.name" placeholder="Enter name" autofocus
                            @keyup.enter="saveAnnotation" />

                        <input type="text" v-model="annotation.store" placeholder="Enter store" autofocus
                            @keyup.enter="saveAnnotation" />

                        <input type="text" v-model="annotation.url" placeholder="Enter url" autofocus
                            @keyup.enter="saveAnnotation" />
                        <button @click="saveAnnotation">Save</button>
                    </div>

                    <div v-if="currentAnnotation != null && !showPopup">
                        <button @click="removeAnnotation(currentAnnotation)">Delete annotation</button>
                        <input type="text" v-model="files[currentImage].annotations[currentAnnotation].name"
                            :placeholder=files[currentImage].annotations[currentAnnotation].name autofocus />

                        <input type="text" v-model="files[currentImage].annotations[currentAnnotation].store"
                            :placeholder=files[currentImage].annotations[currentAnnotation].store autofocus />

                        <input type="text" v-model="files[currentImage].annotations[currentAnnotation].url"
                            :placeholder=files[currentImage].annotations[currentAnnotation].url autofocus />
                    </div>
                </div>
            </div>
        </div>

        <button @click="changeScreen('text')">Volgende</button>
    </div>

    <div v-if="screen === 'text'">
        <button @click="changeScreen('annotations')">Vorige</button>

        <input type="text" v-model="content" placeholder="Enter a text" autofocus />

        <input type="text" v-model="tags" placeholder="Enter tags, seperated with a comma" />
        <button @click="savePost">Save</button>
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
                    store: String,
                    url: String,
                },
                popupStyle: {
                    top: 0,
                    left: 0,
                },
                content: "",
                tags: ""
            };
        },
        props: {
            multiple: Boolean,
        },
        components: {
            draggable,
        },
        methods: {
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
                    top: yPercent,
                    left: xPercent,
                };
                this.annotation = {};
            },
            editAnnotation(index) {
                // console.log(index);
                // console.log(this.files[this.currentImage]);
                // console.log(this.files[this.currentImage].annotations);
                // console.log(this.files[this.currentImage].annotations[index]);
                // console.log(this.files[this.currentImage].annotations[index].annotation);
            },
            showAnnotation(index) {
                this.currentAnnotation = index;
            },
            removeAnnotation(index) {
                this.files[this.currentImage].annotations.splice(index, 1);
                // Ensure currentAnnotation is reset if it was pointing to the removed annotation
                if (this.currentAnnotation === index || this.currentAnnotation >= this.files[this.currentImage]
                    .annotations.length) {
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
                    store: this.annotation.store,
                    url: this.annotation.url,
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
                this.currentAnnotation = this.files[this.currentImage].annotations.length - 1;

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

                console.log(this.screen);

                if (!this.multiple) {
                    this.screen = "annotations";
                }

                console.log(this.screen);

            },
            changeScreen(screen) {
                this.screen = screen;
            },
            async savePost() {
                const formData = new FormData();
                formData.append("content", this.content);
                formData.append("tags", this.tags);

                this.files.forEach((item, index) => {
                    formData.append(`images[${index}]`, item
                        .file); // Use item.file to append the original file
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

                console.log(this.currentImage);

            },
            nextImage() {
                if (this.currentImage < this.files.length - 1) {
                    this.currentImage++;
                    this.currentAnnotation = null;
                }

                console.log(this.currentImage);
            },
        },
    };
</script>
