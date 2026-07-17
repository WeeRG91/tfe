import { ClientDishType } from '@/types/dish';
import { defineStore } from 'pinia';

export const useDishStore = defineStore('dish', {
    state: () => ({
        dishes: [] as ClientDishType[],
    }),

    actions: {
        setDishes(dishes: ClientDishType[]) {
            this.dishes = dishes;
        },

        updateDishRating(dishId: number, average: number, count: number) {
            const dish = this.dishes.find((dish) => dish.id === dishId);

            if (!dish) return;

            dish.rating_average = average;
            dish.rating_count = count;
        },
    },
});
