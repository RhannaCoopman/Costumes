import "./bootstrap";
import { createApp } from "vue";
import Alpine from "alpinejs";

import Like from "./components/Like.vue";
import Save from "./components/Save.vue";
import ImagesUpload from "./components/ImagesUpload.vue";
import Post from "./components/Post.vue";


window.Alpine = Alpine;

const app = createApp({});

app.component("post", Post).component("like", Like).component("save", Save).component("image-upload", ImagesUpload);

app.mount("#app");
Alpine.start();
