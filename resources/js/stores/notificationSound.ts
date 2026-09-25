import { defineStore } from 'pinia';
import { ref } from 'vue';

type NotificationSound = 'order' | 'message';

const STORAGE_KEY = 'admin-notification-sounds-enabled';

export const useNotificationSoundStore = defineStore(
    'notificationSound',
    () => {
        const enabled = ref<boolean>(false);
        const ready = ref<boolean>(false);

        let sounds: Record<NotificationSound, HTMLAudioElement> | null = null;

        const prepare = () => {
            if (typeof window === 'undefined') {
                return;
            }

            if (!sounds) {
                sounds = {
                    order: new Audio('/sounds/new-order.mp3'),
                    message: new Audio('/sounds/new-message.mp3'),
                };

                sounds.order.preload = 'auto';
                sounds.message.preload = 'auto';

                sounds.order.volume = 0.8;
                sounds.message.volume = 0.55;
            }

            enabled.value = window.localStorage.getItem(STORAGE_KEY) === 'true';
        };

        const play = async (type: NotificationSound): Promise<boolean> => {
            prepare();

            if (!enabled.value || !sounds) {
                return false;
            }

            const sound = sounds[type];

            try {
                sound.pause();
                sound.currentTime = 0;

                await sound.play();

                ready.value = true;

                return true;
            } catch (error) {
                console.warn('Notification sound was blocked:', error);

                ready.value = false;

                return false;
            }
        };

        const activate = async (): Promise<boolean> => {
            prepare();

            enabled.value = true;
            window.localStorage.setItem(STORAGE_KEY, 'true');

            return await play('message');
        };

        const disable = () => {
            enabled.value = false;
            ready.value = false;

            if (typeof window !== 'undefined') {
                window.localStorage.setItem(STORAGE_KEY, 'false');
            }
        };

        return {
            enabled,
            ready,
            prepare,
            play,
            activate,
            disable,
        };
    },
);
