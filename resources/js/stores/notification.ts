import notification from '@/routes/notification';
import { CursorPaginated } from '@/types';
import { FilterNotificationEnum, NotificationType } from '@/types/notification';
import axios from 'axios';
import { defineStore } from 'pinia';

export const useNotificationStore = defineStore('notification', {
    state: () => ({
        notifications: [] as NotificationType[],
    }),

    actions: {
        async getNotifications(filter: string, cursor?: string) {
            try {
                const { data } = await axios.get<
                    CursorPaginated<NotificationType>
                >(notification.getNotifications().url, {
                    params: {
                        cursor: cursor,
                        filter: filter,
                    },
                });

                this.notifications = data.data as NotificationType[];

                return data;
            } catch (error) {
                console.error('Failed to get notifications: ', error);
                throw error;
            }
        },

        async markAsRead(id: number) {
            try {
                const {data} = await axios.patch(notification.markAsRead(id).url);

                await this.getNotifications(FilterNotificationEnum.ALL);

                return data;
            } catch (error) {
                console.error('Failed to mark as read: ', error);
                throw error;
            }
        },

        async markAllAsRead() {
            try {
                await axios.patch(notification.markAllAsRead().url);

                await this.getNotifications(FilterNotificationEnum.ALL);
            } catch (error) {
                console.error('Failed to mark all as read: ', error);
                throw error;
            }
        },

        async delete(id: number) {
            try {
                await axios.delete(notification.delete(id).url);

                await this.getNotifications(FilterNotificationEnum.ALL);
            } catch (error) {
                console.error('Failed to delete notification: ', error);
                throw error;
            }
        },

        async deleteAll() {
            try {
                await axios.delete(notification.deleteAll().url);

                await this.getNotifications(FilterNotificationEnum.ALL);
            } catch (error) {
                console.error('Failed to delete all notifications: ', error);
                throw error;
            }
        },
    },
});
