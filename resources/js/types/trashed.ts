export type TrashedModelType = 'Allergen' | 'Dish' | 'Drink' | 'Ingredient' | 'Meat';

export type TrashedType = {
    id: number;
    name: string;
    type: TrashedModelType;
    image: string;
    deleted_at: string;
};
