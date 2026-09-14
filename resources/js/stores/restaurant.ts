import axios from 'axios';
import { defineStore } from 'pinia';

type RestaurantStatus = {
    is_open: boolean;
    accepting_orders: boolean;
    status: string;
    message: string | null;
    checked_at: string;
    timezone: string;
    next_open_at: string | null;
};

type AvailabilityResponse = {
    data: {
        current: RestaurantStatus;
    };
};

export const useRestaurantStore = defineStore('restaurant', {
    state: () => ({
        current: null as RestaurantStatus | null,
        isLoading: false,
        hasError: false,
        isClosedModalOpen: false,
        closedModalMessage: null as string | null,
    }),

    getters: {
        isClosed: (state): boolean =>
            state.current !== null && !state.current.is_open,
        acceptingOrders: (state): boolean =>
            state.current?.accepting_orders ?? false,
    },

    actions: {
        async refresh(): Promise<boolean> {
            if (this.isLoading) {
                return false;
            }

            this.isLoading = true;
            this.hasError = false;

            try {
                const response = await axios.get<AvailabilityResponse>(
                    '/restaurant/availability',
                    {
                        params: { days: 1 },
                    },
                );

                this.current = response.data.data.current;

                return true;
            } catch (error) {
                this.hasError = true;

                console.error(
                    'Failed to fetch restaurant availability:',
                    error,
                );

                return false;
            } finally {
                this.isLoading = false;
            }
        },

        showClosedModal(message: string | null = null) {
            this.closedModalMessage = message;
            this.isClosedModalOpen = true;
        },

        closeClosedModal() {
            this.isClosedModalOpen = false;
            this.closedModalMessage = null;
        },
    },
});
