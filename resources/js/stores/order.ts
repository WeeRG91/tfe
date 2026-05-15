import { OrderType, PlaceOrderPayloadType } from '@/types/order';
import axios from 'axios';
import { defineStore } from 'pinia';

export const useOrderStore = defineStore('order', {
    state: () => ({
        orders: [] as OrderType[],
        currentOrder: null as OrderType | null,
        isLoading: false,
    }),

    actions: {
        async placeOrder(payload: PlaceOrderPayloadType) {
            this.isLoading = true;

            try {
                const { data } = await axios.post(
                    '/orders/place-order',
                    payload,
                );

                this.currentOrder = data.order as OrderType;

                return data;
            } catch (error) {
                console.error('Failed to place order:', error);
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        async getOrders(status: number) {
            this.isLoading = true;

            try {
                const { data } = await axios.get('/orders', {
                    params: { status: status },
                });

                this.orders = data as OrderType[];
            } catch (error) {
                console.error('Failed to fetch orders:', error);
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        async getOrder(orderId: number) {
            this.isLoading = true;

            try {
                const { data } = await axios.get(`/orders/${orderId}`);

                this.currentOrder = data.order as OrderType;

                return data;
            } catch (error) {
                console.error('Failed to fetch order:', error);
                throw error;
            } finally {
                this.isLoading = false;
            }
        },
    },
});
