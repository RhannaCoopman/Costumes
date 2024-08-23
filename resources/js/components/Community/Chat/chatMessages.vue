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

    <div class="flex_col white_color">
        <div class="flex">
            <button class="round_button detail_color image">
                <img :src="'/images/arrow-left.svg'" />
            </button>
            <input
                v-model="newMessage"
                @keyup.enter="sendMessage"
                @keydown="sendTypingEvent"
                type="text"
                placeholder="Type a message..."
                class="light"
            />
            <button @click="sendMessage" class="round_button faded_main_color">
                <img :src="'/images/arrow-left.svg'" />
            </button>
        </div>
        <div class="white_color">
            <small v-if="isTyping"> is typing... </small>
            <small> . </small>

        </div>
    </div>
</template>

<script>
import axios from "axios";
import { nextTick } from "vue"; // Import nextTick

function scrollToBottom() {
    const messagesContainer = document.querySelector(".messages");
    messagesContainer?.scrollTo({
        top: messagesContainer.scrollHeight,
        behavior: "smooth",
    });
}

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
        receiver_id: {
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
            isTyping: false,
            isTypingTimer: null,
        };
    },
    methods: {
        // Send a new message
        async sendMessage() {
            if (this.newMessage.trim() !== "") {
                await axios
                    .post(`/api/chat/${this.chatUuid}/newMessage`, {
                        text: this.newMessage,
                        receiver_id: this.receiver_id,
                    })
                    .then(async (response) => {
                        this.messages.push(response.data);
                        this.newMessage = "";

                        this.sendTypingEvent(false);

                        // Scroll to bottom after message is added
                        await nextTick(); // Ensure DOM updates before scrolling
                        scrollToBottom();
                    })
                    .catch((error) => {
                        console.error("Error posting data:", error);
                    });
            }
        },

        // Fetch messages with pagination
        async fetchMessages(page = 1) {
            if (this.isLoading) return;

            this.isLoading = true;
            try {
                const response = await axios.get(`/api/chat/${this.chatUuid}`, {
                    params: { page },
                });

                const fetchedMessages = response.data.messages.data.reverse(); // Reverse for correct order

                if (fetchedMessages.length === 0) {
                    this.hasMoreMessages = false;
                } else {
                    this.messages = [...fetchedMessages, ...this.messages];
                    this.page++;
                }

                // Scroll to bottom after initial fetch
                await nextTick(); // Ensure DOM updates before scrolling
                scrollToBottom();
            } catch (error) {
                console.error("Error fetching data:", error);
            } finally {
                this.isLoading = false;
            }
        },

        // Send a typing event to the server
        sendTypingEvent(isTyping = true) {
            Echo.private(`chat.${this.receiver_id}`).whisper("typing", {
                userID: this.loggedInUserId,
                isTyping: isTyping,
            });
        },
        populateSenderData(message) {
            // Find the sender in the existing messages
            const sender = this.messages.find(
                (msg) => msg.sender && msg.sender.id === message.sender_id
            )?.sender;

            // If sender data is found, attach it to the message
            if (sender) {
                message.sender = sender;
            } else {
                // If no sender found, you can either make an API call to fetch the sender
                // Or leave the sender as null and display "Unknown user"
                message.sender = { name: "Unknown user" };
            }
        },
    },
    watch: {
        messages: {
            async handler() {
                await nextTick(); // Ensure DOM updates before scrolling
                scrollToBottom();
            },
            deep: true,
        },
        newMessage(newValue) {
            // If the newMessage is being typed, send a typing event
            if (newValue.trim() !== "") {
                this.sendTypingEvent(true);
            }
        },
    },
    mounted() {
        // Fetch initial messages
        this.fetchMessages();

        // Listen for real-time message events
        Echo.private(`chat.${this.loggedInUserId}`)
            .listen("MessageSent", async (response) => {
                // Populate the sender data before pushing the message
                this.populateSenderData(response.message);

                this.messages.push(response.message);

                // Scroll to bottom after receiving a new message
                await nextTick(); // Ensure DOM updates before scrolling
                scrollToBottom();
            })
            // Listen for typing events
            .listenForWhisper("typing", (response) => {
                if (response.userID !== this.loggedInUserId) {
                    this.isTyping = response.isTyping;

                    // Reset typing indicator after a timeout
                    if (this.isTypingTimer) {
                        clearTimeout(this.isTypingTimer);
                    }

                    this.isTypingTimer = setTimeout(() => {
                        this.isTyping = false;
                    }, 1500);
                }
            });
    },
};
</script>
