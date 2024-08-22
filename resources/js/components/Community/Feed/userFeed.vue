<!-- userFeed Component -->
<!-- <template>
    <p>UserFeed</p>

    <div @scroll="onScroll" class="infinite_container">
        <ul class="infinite_list">
            <li class="infinite_list_item" v-for="user in users" :key="user.id">
                <div href="" class="feed_post">
                    <p class="title">{{ user.name }}</p>

                    <div class="imagegrid">

                    </div>

                    <div class="">
                        <chat :user-id="user.id" :chat-uuid="user.chat_uuid"></chat>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</template> -->

<template>
    <div class="flex_column">
        <div class="" v-for="user in users" :key="user.id">
            <div class="flex_column rounded_corners_container white_color">
                <div class="flex">
                    <div class="small_round_container">
                        <img
                            src="../../../../../storage/app/public/uploads/IVzpf5wx7YzMsqEc1kTd3BfFWE2TwU7gEP0v9UQu.png"
                        />
                    </div>
                    <h3 class="">{{ user.user.name }}</h3>
                </div>

                <!-- <div class="scroll-container">
                        <div class="grid">
                            <a class="item" href="">
                                <img src="https://www.google.com/url?sa=i&url=https%3A%2F%2Fthecatisinthebox.com%2Fblogs%2Fkitty-contemplations%2Fthe-tabby-cat&psig=AOvVaw0hYdfz3gKWtdJvw_jZUcl_&ust=1720688135841000&source=images&cd=vfe&opi=89978449&ved=0CBQQjRxqFwoTCPj50fyMnIcDFQAAAAAdAAAAABAJ" />
                            </a>

                            <a class="item" href="">
                                <img src="https://www.google.com/url?sa=i&url=https%3A%2F%2Fen.wikipedia.org%2Fwiki%2FTabby_cat&psig=AOvVaw0hYdfz3gKWtdJvw_jZUcl_&ust=1720688135841000&source=images&cd=vfe&opi=89978449&ved=0CBQQjRxqFwoTCPj50fyMnIcDFQAAAAAdAAAAABAE" />
                            </a>

                            <a class="item" href="">
                                <img src="https://www.google.com/url?sa=i&url=https%3A%2F%2Fen.wikipedia.org%2Fwiki%2FTabby_cat&psig=AOvVaw0hYdfz3gKWtdJvw_jZUcl_&ust=1720688135841000&source=images&cd=vfe&opi=89978449&ved=0CBQQjRxqFwoTCPj50fyMnIcDFQAAAAAdAAAAABAE" />
                            </a>
                        </div>
                    </div> -->

                <div class="flex">
                    <chat
                        :user-id="user.user.id"
                        :chat-uuid="user.chat_uuid"
                    ></chat>
                    <chat
                        :user-id="user.user.id"
                        :chat-uuid="user.chat_uuid"
                    ></chat>
                    <chat
                        :user-id="user.user.id"
                        :chat-uuid="user.chat_uuid"
                    ></chat>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "userFeed",
    props: {
        users: {
            type: Array,
            required: true,
        },
    },
    methods: {
        onScroll(event) {
            const container = event.target;
            if (
                container.scrollTop + container.clientHeight >=
                container.scrollHeight - 10
            ) {
                this.$emit("load-more");
            }
        },
    },
    mounted() {
        console.log(this.users);
    },
};
</script>
