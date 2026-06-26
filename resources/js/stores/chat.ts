import chat from '@/routes/admin/chat';
import { ChatType } from '@/types/chat';
import axios from 'axios';
import { defineStore } from 'pinia';

export const useChatStore = defineStore('chat', {
    state: () => ({
        chats: [] as ChatType[],
        isLoading: false,
    }),

    actions: {
        async fetchChats() {
            this.isLoading = true;

            try {
                const { data } = await axios.get<ChatType[]>(
                    chat.getChats().url,
                );

                this.chats = data;
            } catch (error) {
                console.error(error);
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        async markAsRead(chatId: number) {
            const currentChat = this.chats.find((c) => c.id === chatId);

            if (!currentChat?.latest_message) {
                return;
            }

            if (
                currentChat.latest_message.read_at !== null ||
                currentChat?.latest_message.is_from_restaurant
            ) {
                return;
            }

            try {
                await axios.patch(chat.markAsRead(chatId).url);

                await this.fetchChats();
            } catch (error) {
                console.error(error);
                throw error;
            }
        },
    },
});
