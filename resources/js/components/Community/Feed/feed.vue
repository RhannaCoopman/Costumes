<template>
    <div class="community_feed_navigation">
        <toggle-views @screen-changed="changeScreen"></toggle-views>
        <community-filter @filters-applied="applyFilters"></community-filter>
    </div>

    <div class="messages">
        <group-feed v-if="!loading && screen === 'group'"></group-feed>
        <user-feed v-if="!loading && screen === 'user'" :users="users"></user-feed>
    </div>
</template>

<script>
import axios from "axios";

export default {
    name: "Feed",
    data() {
        return {
            screen: "user",
            users: [],
            loading: false,
            page: 1,
            perPage: 50,
            allUsersLoaded: false,
            selectedInterests: [],
            location: {
                city: "",
                distance: 50,
                everywhere: true
            }
        };
    },
    methods: {
        changeScreen(screen) {
            this.screen = screen;
        },
        async fetchUsers() {
            this.loading = true;
            try {
                const response = await axios.get("/api/community/users", {
                    params: {
                        page: this.page,
                        per_page: this.perPage,
                        interests: this.selectedInterests, // Pass selected interests as a comma-separated string
                        city: this.location.city,
                        zipcode: this.location.zipcode,
                        distance: this.location.distance,
                        everywhere: this.location.everywhere
                    },
                });

                const fetchedUsers = response.data.data;

                if (fetchedUsers.length === 0) {
                    this.allUsersLoaded = true;
                } else {
                    this.users = [...this.users, ...fetchedUsers];
                    this.page++;
                }
            } catch (error) {
                console.error("Error fetching users:", error);
            } finally {
                this.loading = false;
            }
        },
        fetchMoreUsers() {
            if (!this.loading && !this.allUsersLoaded) {
                this.fetchUsers();
            }
        },
        applyFilters(filters) {
            this.selectedInterests = filters.selectedInterests;
            this.location = filters.location;
            this.page = 1;
            this.users = [];
            this.allUsersLoaded = false;
            this.fetchUsers();
        }
    },
    mounted() {
        this.fetchUsers();
    },
};
</script>
