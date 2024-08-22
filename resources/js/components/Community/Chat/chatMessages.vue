<template>
    <div class="messages" @scroll="handleScroll">
        <div
            v-for="(message, index) in messages"
            :key="'m-' + index"
            class="message_card"
            :class="[
                message.sender_id === this.loggedInUserId ? 'align_right' : '',
            ]"
        >
            <div class="chat_heading flex">
                <div class="small_round_container">
                    <img
                        src="../../../../../storage/app/public/uploads/IVzpf5wx7YzMsqEc1kTd3BfFWE2TwU7gEP0v9UQu.png"
                    />
                </div>
                <p>{{ message.sender?.name ?? "Unknown user" }}</p>
                <img :src="'/images/liked.svg'" />
                <p class="light_font">{{ message.created_at }}</p>
            </div>
            <div
                class="chat_content rounded_corners_container white_color fit_content"
            >
                <p>{{ message.message }}</p>
            </div>
        </div>
    </div>

    <div class="flex white_color">
        <button class="round_button detail_color image">
            <img :src="'/images/arrow-left.svg'" />
        </button>
        <input
            v-model="newMessage"
            @keyup.enter="sendMessage"
            type="text"
            placeholder="Type a message..."
            class="light"
        />
        <button @click="sendMessage" class="round_button faded_main_color">
            <img :src="'/images/arrow-left.svg'" />
        </button>
    </div>
</template>

<script>
import axios from "axios";

export default {
    props: {
        chatUuid: {
            type: String,
            required: true,
        },
        loggedInUserId: {
            type: Number,
            required: true,
        },
    },
    data() {
        return {
            messages: [],
            newMessage: "",
            page: 1,
            hasMoreMessages: true,
            isLoading: false,
        };
    },
    methods: {
        async sendMessage() {
            if (this.newMessage.trim() !== "") {
                await axios
                    .post(`/api/chat/${this.chatUuid}/newMessage`, {
                        text: this.newMessage,
                    })
                    .then((response) => {
                        this.messages.push(response.data);
                        this.newMessage = "";
                        // setTimeout(() => {
                        //     this.scrollToBottom();
                        // }, 150);
                    })
                    .catch((error) => {
                        console.error("Error posting data:", error);
                    });
            }
        },
        async fetchMessages(page = 1) {
            if (this.isLoading) return;

            this.isLoading = true;
            try {
                const response = await axios.get(`/api/chat/${this.chatUuid}`, {
                    params: { page },
                });

                const fetchedMessages = response.data.messages.data;

                if (fetchedMessages.length === 0) {
                    this.hasMoreMessages = false;
                } else {
                    this.messages = [...this.messages, ...fetchedMessages];
                    this.page++;
                }
            } catch (error) {
                console.error("Error fetching data:", error);
            } finally {
                this.isLoading = false;
            }
        },
        // scrollToBottom() {
        //     const messagesContainer = document.querySelector(".messages");
        //     messagesContainer.scrollTo(0, messagesContainer.scrollHeight);
        // },
        // handleScroll(event) {
        //     // console.log(event.target);
        //     const { scrollEnd } = event.target;
        //     console.log(scrollEnd);

        //     // if (scrollTop === 0) {
        //     //     if (this.hasMoreMessages) {
        //     //         this.fetchMessages(this.page);
        //     //     }
        //     // }
        // },
        handleScroll(event) {
                const { scrollTop, scrollHeight, clientHeight } = event.target;

                if (scrollTop + clientHeight >= scrollHeight - 20) {
                    if (this.hasMoreMessages) {
                        this.fetchMessages(this.page);
                    }
                }
            }
    },
    mounted() {
        this.fetchMessages();
        // setTimeout(() => {
        //     this.scrollToBottom();
        // }, 150);
    },
};
</script>
