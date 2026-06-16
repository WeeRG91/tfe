import { CartItemType, ItemTypeEnum } from '@/types/cart';
import { computed, Ref } from 'vue';
import { ALCOHOL_CATEGORIES, AlcoholCategory } from '@/types/drink';
import { OrderItemType, OrderTypeEnum } from '@/types/order';

export function useVatCalculator(params: {
    items: Ref<CartItemType[] | OrderItemType[]>;
    discountAmount: Ref<number>;
    deliveryFee: Ref<number>;
    selectedOrderTypeValue: Ref<number | null>;
}) {
    const vat_12 = 0.12;
    const vat_21 = 0.21;
    const { items, discountAmount, deliveryFee, selectedOrderTypeValue } =
        params;

    const dishItems = computed(() =>
        items.value.filter((i) => i.item_type === ItemTypeEnum.DISH),
    );
    const alcoholicItems = computed(() =>
        items.value.filter(
            (i) =>
                i.item_type === ItemTypeEnum.DRINK &&
                ALCOHOL_CATEGORIES.includes(i.item.category.label as AlcoholCategory),
        ),
    );
    const nonAlcoholicItems = computed(() =>
        items.value.filter(
            (i) =>
                i.item_type === ItemTypeEnum.DRINK &&
                !ALCOHOL_CATEGORIES.includes(i.item.category.label as AlcoholCategory),
        ),
    );

    const total12 = computed(() => {
        const food = dishItems.value.reduce(
            (sum, i) => sum + (i.total_inc_vat || 0),
            0,
        );
        const nonAlcoholic = nonAlcoholicItems.value.reduce(
            (sum, i) => sum + (i.total_inc_vat || 0),
            0,
        );

        return food + nonAlcoholic;
    });
    const total21 = computed(() => {
        const alcoholic = alcoholicItems.value.reduce(
            (sum, i) => sum + (i.total_inc_vat || 0),
            0,
        );
        const delivery =
            selectedOrderTypeValue.value === OrderTypeEnum.DELIVERY ? deliveryFee.value : 0;

        return alcoholic + delivery;
    });

    const subtotalBeforeDeliveryFee = computed(() => total12.value + total21.value - deliveryFee.value);
    const subtotal = computed(() => total12.value + total21.value);

    const totalAfterDiscount = computed(() => {
        return Math.max(subtotal.value - discountAmount.value, 0);
    });

    const discountRatio = computed(() => {
        if (subtotal.value <= 0) return 0;
        return totalAfterDiscount.value / subtotal.value;
    });

    const total12AfterDiscount = computed(
        () => total12.value * discountRatio.value,
    );

    const total21AfterDiscount = computed(
        () => total21.value * discountRatio.value,
    );

    const net12 = computed(() => total12AfterDiscount.value / (1 + vat_12));
    const vat12Total = computed(() => total12AfterDiscount.value - net12.value);

    const net21 = computed(() => total21AfterDiscount.value / (1 + vat_21));
    const vat21Total = computed(() => total21AfterDiscount.value - net21.value);

    const totalVat = computed(() => vat12Total.value + vat21Total.value);

    const totalIncVat = computed(() => totalAfterDiscount.value);

    return {
        vat12Total,
        vat21Total,
        totalVat,
        subtotalBeforeDeliveryFee,
        totalIncVat,
    };
}
