<template>
    <div>
        <slot :items="items"></slot>
        <div ref="loadMoreTrigger" v-if="!allItemsLoaded" class="loading">Loading more...</div>
    </div>
</template>

<script>
export default {
    name: "InfiniteScroll",
    props: {
        fetchFunction: {
            type: Function,
            required: true
        },
        perPage: {
            type: Number,
            default: 10
        }
    },
    data() {
        return {
            items: [],
            page: 1,
            loading: false,
            allItemsLoaded: false
        };
    },
    methods: {
        fetchItems() {
            if (this.loading || this.allItemsLoaded) return;

            this.loading = true;
            setTimeout(() => {
                this.fetchFunction(this.page, this.perPage)
                    .then(data => {
                        if (data.length < this.perPage) {
                            this.allItemsLoaded = true;
                        }
                        this.items = [...this.items, ...data];
                        this.loading = false;
                    })
                    .catch(error => {
                        console.error('Error fetching items:', error);
                        this.loading = false;
                    });
            }, 1000);
        },
        setupIntersectionObserver() {
            this.$nextTick(() => {
                const loadMoreTrigger = this.$refs.loadMoreTrigger;
                if (loadMoreTrigger) {
                    const options = {
                        root: null,
                        rootMargin: '0px',
                        threshold: 1.0
                    };

                    const observer = new IntersectionObserver((entries, observer) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                observer.unobserve(entry.target);
                                this.page += 1;
                                this.fetchItems();
                                this.$nextTick(() => {
                                    observer.observe(entry.target);
                                });
                            }
                        });
                    }, options);

                    observer.observe(loadMoreTrigger);
                }
            });
        }
    },
    mounted() {
        this.fetchItems();
        this.setupIntersectionObserver();
    }
};
</script>

<style>
.loading {
    text-align: center;
    font-size: 1.5em;
    padding: 20px;
}
</style>
