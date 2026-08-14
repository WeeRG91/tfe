import confirmedOrder from '@/routes/admin/confirmed-order';
import order from '@/routes/order';
import {
    OrderStatusEnum,
    OrderType,
    PlaceOrderPayloadType,
    ReorderPayloadType,
} from '@/types/order';
import axios from 'axios';
import { defineStore } from 'pinia';

export const useOrderStore = defineStore('order', {
    state: () => ({
        orders: [] as OrderType[],
        confirmedOrders: [] as OrderType[],
        currentOrder: null as OrderType | null,
        isLoading: false,
    }),

    actions: {
        async placeOrder(payload: PlaceOrderPayloadType) {
            this.isLoading = true;

            try {
                const { data } = await axios.post(
                    order.placeOrder().url,
                    payload,
                );

                this.currentOrder = data.order as OrderType;

                return data;
            } catch (error) {
                console.error('Failed to place order: ', error);
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        async confirmReorder(payload: ReorderPayloadType) {
            this.isLoading = true;

            try {
                const { data } = await axios.post(
                    order.confirmReorder().url,
                    payload,
                );

                this.currentOrder = data.order as OrderType;

                return data;
            } catch (error) {
                console.error('Failed to reorder: ', error);
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        async getOrders(status: string) {
            this.isLoading = true;

            try {
                const { data } = await axios.get(order.getOrders().url, {
                    params: { status: status },
                });

                this.orders = data as OrderType[];
            } catch (error) {
                console.error('Failed to fetch orders: ', error);
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        async getOrder(orderId: number) {
            this.isLoading = true;

            try {
                const { data } = await axios.get(order.getOrder(orderId).url);

                this.currentOrder = data.order as OrderType;

                return data;
            } catch (error) {
                console.error('Failed to fetch order: ', error);
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        async cancel(orderId: number) {
            this.isLoading = true;

            try {
                const {data} = await axios.patch(order.cancel(orderId).url);

                return data;
            } catch (error) {
                console.error('Failed to cancel order: ', error);
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        async remove(orderId: number) {
            this.isLoading = true;

            try {
                const {data} = await axios.delete(order.destroy(orderId).url);

                return data;
            } catch (error) {
                console.error('Failed to delete order: ', error);
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        async getConfirmedOrders() {
            this.isLoading = true;

            try {
                const { data } = await axios.get(
                    confirmedOrder.getConfirmedOrders().url,
                );

                this.confirmedOrders = data as OrderType[];
            } catch (error) {
                console.error('Failed to fetch confirmed orders: ', error);
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        async cancelConfirmedOrder(orderId: number) {
            this.isLoading = true;

            try {
                const {data} = await axios.patch(confirmedOrder.cancel(orderId).url);

                return data;
            } catch (error) {
                console.error('Failed to cancel order: ', error);
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        async updateOrderStatus(orderId: number, status: OrderStatusEnum) {
            this.isLoading = true;

            try {
                const { data } = await axios.post(
                    confirmedOrder.updateOrderStatus(orderId).url,
                    { newStatus: status },
                );

                await this.getConfirmedOrders();

                return data;
            } catch (error) {
                console.error('Failed to update order status: ', error);
                throw error;
            } finally {
                this.isLoading = false;
            }
        },
    },
});
