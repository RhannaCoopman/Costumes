<template>
    <section>
        <!-- Welcome Screen -->
        <div v-if="screen === 1" class="rounded_corners_container white_color">
            <div class="screen_navigation">
                <div></div>

                <div @click="changeScreen(2)">
                    <p>Next</p>
                    <img :src="'/images/arrow-right.svg'" />
                </div>
            </div>

            <h1>Welkom</h1>
            <p>
                Welkom bij <b>Costumes</b>, the place to be voor vrouwen opzoek
                naar kostuums.
                <br>
                Gebruik de <img :src="'/images/liked.svg'" />-button om outfits te liken, en de <img :src="'/images/saved.svg'" />-button om outfits op te slaan. Je vindt je opgeslagen outfits op je profielpagina.
                <br>
            </p>
        </div>

        <!-- Posts Screen -->
        <div v-if="screen === 2" class="rounded_corners_container white_color">
            <div class="screen_navigation">
                <div>
                    <div class="round_button faded_white_color">
                        <img
                            @click="changeScreen(1)"
                            :src="'/images/arrow-left.svg'"
                        />
                    </div>
                    <h1>Annotations</h1>
                </div>

                <div @click="changeScreen(3)">
                    <p>Next</p>
                    <img :src="'/images/arrow-right.svg'" />
                </div>
            </div>

            <p>Kies 3 tot 10 outfits die je leuk <img :src="'/images/liked.svg'" /> vindt, op basis hiervan zullen we je feed personaliseren!</p>

            <div class="feed_grid">
                <div v-for="post in posts" :key="post.id" class="feed_post">
                    <div class="feed_post">
                        <div v-if="post.is_popular" class="post_badge">
                            <img src="../../../../public/images/crown.svg" />
                            <p class="badge_text">Most liked today</p>
                        </div>

                        <div class="post_image">
                            <img
                                v-if="post.first_image.path"
                                :src="getImageUrl(post.first_image.path)"
                                alt="Post Image"
                            />

                            <div class="actions">
                                <like
                                    class="like"
                                    :post-id="post.id"
                                    :initial-liked="false"
                                ></like>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="this.isLoading" class="loading"></div>
            </div>
        </div>

        <!-- Interests Screen -->
        <div v-if="screen === 3" class="rounded_corners_container white_color">
            <div class="screen_navigation">
                <div>
                    <div class="round_button faded_white_color">
                        <img
                            @click="changeScreen(2)"
                            :src="'/images/arrow-left.svg'"
                        />
                    </div>
                    <h1>Choose Interests</h1>
                </div>

                <div @click="save">
                    <p>Finish</p>
                    <img :src="'/images/arrow-right.svg'" />
                </div>
            </div>

            <p>Een belangrijk deel van de app is community. Kies hier je voorkeuren voor community:
                <br>
                Wil je graag chatten over vanalles, kies dan chatting. wil je graag memes, video... sturen en krijgen, kies voor memes. Wil je graag je outfits showen en feedback krijgen, of wil jij graag feedback en je mening geven aan anderen, kies dan voor advice. Wil je aanraders van winkels, creator of outfits krijgen en/of geven, kies dan voor recommandations. Wil je graag met mensen af spreken om samen te gaan shoppen, kies dan voor meet-ups.
                <br>
                Natuurlijk kan je er ook meerdere of allemaal kiezen!
            </p>
            <br>

            <div class="flex_column">
                <div class="flex">
                    <img src="../../../public/images/liked.svg" />
                    <p>Interests</p>
                </div>

                <div class="flex_column">
                    <div
                        v-for="interest in allInterests"
                        :key="interest.id"
                        class="checkbox"
                    >
                        <input
                            :id="'interest-' + interest.id"
                            class="box"
                            type="checkbox"
                            :value="interest.id"
                            v-model="selectedInterests"
                        />
                        <label :for="'interest-' + interest.id">{{
                            interest.name
                        }}</label>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import axios from "axios";

export default {
    data() {
        return {
            posts: [],
            isLoading: false,
            screen: 1,
            allInterests: [],
            selectedInterests: [],
        };
    },

    methods: {
        changeScreen(screen) {
            this.screen = screen;
        },
        async fetchPosts() {
            if (this.isLoading) return;

            this.isLoading = true;

            try {
                const response = await axios.get("/api/welcome_posts", {});
                this.posts = response.data.data;
                this.allInterests = response.data.allInterests;
            } catch (error) {
                console.error("Error fetching data:", error);
            } finally {
                this.isLoading = false;
            }
        },
        async save() {
            try {
                // Save interests and mark the welcome flow as completed
                const response = await axios.post("/api/save_welcome_data", {
                    interests: this.selectedInterests,
                });

                location.reload();
                this.screen = 0; // Hides the welcome flow sections
            } catch (error) {
                console.error("Error saving data:", error);
            }
        },
        getImageUrl(path) {
            return `/storage/${path}`;
        },
    },
    mounted() {
        this.fetchPosts();
    },
};
</script>
