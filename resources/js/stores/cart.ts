import { AddDishToCartType, AddDrinkToCartType, CartType } from '@/types/cart';
import axios from 'axios';
import { defineStore } from 'pinia';

export const useCartStore = defineStore('cart', {
    state: () => ({
        cart: null as CartType | null,
        isLoading: false,
    }),

    getters: {
        items: (state) => state.cart?.items ?? [],

        cartItemCount: (state) => state.cart?.items?.length ?? 0,
    },

    actions: {
        async getCart() {
            this.isLoading = true;

            try {
                const guestToken = localStorage.getItem('guest_token');

                const { data } = await axios.get('/cart/get-cart', {
                    headers: guestToken ? { 'X-Guest-Token': guestToken } : {},
                });

                this.cart = data as CartType;

                if (data.guest_token) {
                    localStorage.setItem('guest_token', data.guest_token);
                }
            } catch (error) {
                console.error('Failed to fetch cart: ', error);
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        async addDish(payload: AddDishToCartType) {
            this.isLoading = true;

            try {
                const guestToken = localStorage.getItem('guest_token');

                const { data } = await axios.post(
                    '/cart/items/add-dish',
                    payload,
                    {
                        headers: guestToken
                            ? { 'X-Guest-Token': guestToken }
                            : {},
                    },
                );

                await this.getCart();

                return data;
            } catch (error) {
                console.log('Failed to add item: ', error);
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        async addDrink(payload: AddDrinkToCartType) {
            this.isLoading = true;

            try {
                const guestToken = localStorage.getItem('guest_token');

                const { data } = await axios.post(
                    '/cart/items/add-drink',
                    payload,
                    {
                        headers: guestToken
                            ? { 'X-Guest-Token': guestToken }
                            : {},
                    },
                );

                await this.getCart();

                return data;
            } catch (error) {
                console.log('Failed to add item: ', error);
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        async removeItem(cartItemId: number) {
            try {
                const guestToken = localStorage.getItem('guest_token');

                const { data } = await axios.delete(
                    `/cart/items/${cartItemId}`,
                    {
                        headers: guestToken
                            ? { 'X-Guest-Token': guestToken }
                            : {},
                    },
                );

                this.cart!.items = this.cart!.items.filter(
                    (i) => i.id !== cartItemId,
                );

                return data;
            } catch (error) {
                console.log('Failed to remove item: ', error);
                throw error;
            }
        },

        async updateNotes(cartItemId: number, notes: string) {
            this.isLoading = true;

            try {
                const guestToken = localStorage.getItem('guest_token');

                await axios.patch(
                    `/cart/items/${cartItemId}/notes`,
                    { notes },
                    {
                        headers: guestToken
                            ? { 'X-Guest-Token': guestToken }
                            : {},
                    },
                );

                const item = this.cart?.items.find((i) => i.id === cartItemId);
                if (item) {
                    item.notes = notes;
                }
            } catch (error) {
                console.log('Failed to update notes: ', error);
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        async updateQuantity(
            cartItemId: number,
            action: 'increase' | 'decrease',
        ) {
            try {
                const guestToken = localStorage.getItem('guestToken');
                await axios.patch(
                    `/cart/items/${cartItemId}/quantity`,
                    { action },
                    {
                        headers: guestToken
                            ? { 'X-Guest-Token': guestToken }
                            : {},
                    },
                );

                const item = this.cart?.items.find((i) => i.id === cartItemId);

                if (!item) return;

                if (action === 'increase') {
                    item.quantity += 1;
                } else {
                    if (item.quantity <= 1) {
                        this.cart!.items = this.cart!.items.filter(
                            (i) => i.id !== cartItemId,
                        );
                        return;
                    }

                    item.quantity -= 1;
                }

                item.total_inc_vat = Number(item.unit_price) * item.quantity;
            } catch (error) {
                console.log('Failed to update quantity: ', error);
                throw error;
            }
        },
    },
});
