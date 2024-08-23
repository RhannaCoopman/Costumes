<template>
    <div class="flex-column relative">
        <div class="flex white_color round_container" @click="toggleFilter">
        <img src="../../../../../public/images/liked.svg" />
        <p>Filters</p>
    </div>

    <div v-if="showFilters" class="absolute flex_column white_color round_container">
        <!-- Location -->
        <div class="flex_column">
            <div class="flex">
                <img src="../../../../../public/images/liked.svg" />
                <p>Location</p>
            </div>
            <div class="checkbox">
                <input
                    id="everywhere"
                    class="box"
                    type="checkbox"
                    v-model="location.everywhere"
                    aria-hidden="true"
                />
                <label for="everywhere">Everywhere</label>
            </div>
            <div class="flex" v-if="!location.everywhere">
                <div class="input">
                    <label>City</label>
                    <input type="text" v-model="location.city" autofocus />
                </div>

                <div class="input">
                    <label>Distance (km)</label>
                    <input type="number" v-model="location.distance" autofocus />
                </div>
            </div>
        </div>

        <!-- Interests -->
        <div class="flex_column">
            <div class="flex">
                <img src="../../../../../public/images/liked.svg" />
                <p>Interests</p>
            </div>

            <div class="flex_column">
                <div v-for="interest in allInterests" :key="interest.id" class="checkbox">
                    <input
                        :id="'interest-' + interest.id"
                        class="box"
                        type="checkbox"
                        :value="interest.id"
                        v-model="selectedInterests"
                    />
                    <label :for="'interest-' + interest.id">{{ interest.name }}</label>
                </div>
            </div>
        </div>

        <!-- Apply filters button -->
        <button @click="applyFilters">Apply filters</button>
    </div>
    </div>

</template>

<script>
export default {
    name: "communityFilter",
    emits: ["filters-applied"],
    data() {
        return {
            location: {
                city: "",
                zipcode: "",
                distance: 50,
                everywhere: true
            },
            allInterests: [],
            selectedInterests: [], // This array will store the IDs of selected interests
            showFilters: false,
        };
    },
    created() {
        this.fetchInterestsData();
    },
    methods: {
        fetchInterestsData() {
            fetch(`/api/community/interests`)
                .then(response => response.json())
                .then(data => {
                    this.allInterests = data.allInterests;
                    this.selectedInterests = data.userInterests;
                    this.location.city = data.city;
                    this.location.zipcode = data.zip_code;
                    this.applyFilters();
                })
                .catch(error => {
                    console.error('Error fetching interests data:', error);
                });
        },
        toggleFilter() {
            if (this.showFilters) {
                this.showFilters = false;
                return;
            }

            this.showFilters = true;

        },
        applyFilters() {
            this.$emit('filters-applied', {

                location: this.location,
                selectedInterests: this.selectedInterests,
            });
        }
    }
};
</script>
