<template>
    <div>
        <button @click="openChat" class="round_button faded_main_color">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-message-circle" width="24"
                height="24" viewBox="0 0 24 24" stroke-width="1.5" :stroke="chat_uuid ? '#F7DD6F' : '#faf8f637'"
                fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path
                    d="M3 20l1.3 -3.9c-2.324 -3.437 -1.426 -7.872 2.1 -10.374c3.526 -2.501 8.59 -2.296 11.845 .48c3.255 2.777 3.695 7.266 1.029 10.501c-2.666 3.235 -7.615 4.215 -11.574 2.293l-4.7 1" />
            </svg>
        </button>
    </div>
</template>

<script>
    import axios from 'axios';

    export default {
        name: "Chat",
        props: {
            userId: {
                type: Number,
                required: false
            },
            chatUuid: {
                type: [String, null],
                required: false,
                default: null
            },
        },
        data() {
            return {
                chat_uuid: this.chatUuid,
            };
        },
        methods: {
            async openChat() {
                if (!this.chat_uuid) {
                    // If chat_uuid is null, create a new chat
                    try {
                        const response = await axios.post('/api/chat/create', {user_id: this.userId});

                        this.chat_uuid = response.data.uuid;
                    } catch (error) {
                        console.error('Error creating new chat:', error);
                    }
                }
                window.location.href = "/chat/" + this.chat_uuid;
            }
        },
    };
</script>
