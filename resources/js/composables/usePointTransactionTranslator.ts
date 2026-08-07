import type { LoyaltyPointTransactionType } from '@/types/point';
import { useI18n } from 'vue-i18n';

export function usePointTransactionTranslator() {
    const { t, te } = useI18n();

    const translatePointTransaction = (
        transaction: LoyaltyPointTransactionType,
    ): string => {
        const key = `point.transactions.${transaction.type.key}`;

        if (!transaction.order_number || !te(key)) {
            return transaction.description;
        }

        return t(key, { orderNumber: transaction.order_number });
    };

    return { translatePointTransaction };
}
