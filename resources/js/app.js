import "./bootstrap";
import { createApp } from "vue";
import Alpine from "alpinejs";

import Like from "./components/Like.vue";
import Chat from "./components/Chat.vue";
import Save from "./components/Save.vue";
import ImagesUpload from "./components/ImagesUpload.vue";
import Post from "./components/Post.vue";

import feed from "./components/Community/Feed/feed.vue"
import communityFilter from "./components/Community/Feed/communityFilter.vue"
import groupFeed from "./components/Community/Feed/groupFeed.vue"
import userFeed from "./components/Community/Feed/userFeed.vue"
import toggleViews from "./components/Community/Feed/toggleViews.vue"
import chatMessages from "./components/Community/Chat/chatMessages.vue"

import FeedGrid from "./components/Post/FeedGrid.vue"
import FeedFilters from "./components/Post/FeedFilters.vue"

import WelcomeFlow from "./components/WelcomeFlow.vue"


window.Alpine = Alpine;

const app = createApp({});

app
.component("post", Post)
.component("like", Like)
.component("save", Save)
.component("chat", Chat)
.component("image-upload",ImagesUpload)
.component("feed", feed)
.component("community-filter", communityFilter)
.component("group-feed", groupFeed)
.component("user-feed",userFeed)
.component("toggle-views", toggleViews)
.component("chat-messages", chatMessages)
.component("feed-grid", FeedGrid)
.component("feed-filters", FeedFilters)
.component("welcome-flow", WelcomeFlow)
;





app.mount("#app");
Alpine.start();
