import { OrderStatusEnum, OrderTypeEnum } from '@/types/order';
import { useI18n } from 'vue-i18n';

enum ActionEnum {
    STARTPREPARING = 'start-preparing',
    MARKASREADY = 'mark-as-ready',
    STARTDELIVERING = 'start-delivering',
    COMPLETEORDER = 'complete-order',
    UNDO = 'undo',
}

export function useOrderStatusActionFlow(
    updateStatus: (orderStatus: OrderStatusEnum, actionId: string) => void,
) {
    const { t } = useI18n();

    const getPreviousStatus = (status: OrderStatusEnum) => {
        const PREVIOUS_STATUS: Record<OrderStatusEnum, OrderStatusEnum | null> =
            {
                [OrderStatusEnum.PENDING]: null,
                [OrderStatusEnum.CONFIRMED]: null,
                [OrderStatusEnum.PREPARING]: OrderStatusEnum.CONFIRMED,
                [OrderStatusEnum.READY]: OrderStatusEnum.PREPARING,
                [OrderStatusEnum.DELIVERING]: OrderStatusEnum.READY,
                [OrderStatusEnum.COMPLETED]: OrderStatusEnum.DELIVERING,
                [OrderStatusEnum.CANCELLED]: null,
            };
        return PREVIOUS_STATUS[status];
    };

    const getNextStatus = (status: OrderStatusEnum, type: OrderTypeEnum) => {
        const NEXT_STATUS: Record<OrderStatusEnum, OrderStatusEnum | null> = {
            [OrderStatusEnum.PENDING]: OrderStatusEnum.CONFIRMED,
            [OrderStatusEnum.CONFIRMED]: OrderStatusEnum.PREPARING,
            [OrderStatusEnum.PREPARING]: OrderStatusEnum.READY,
            [OrderStatusEnum.READY]:
                type === OrderTypeEnum.DELIVERY
                    ? OrderStatusEnum.DELIVERING
                    : OrderStatusEnum.COMPLETED,
            [OrderStatusEnum.DELIVERING]: OrderStatusEnum.COMPLETED,
            [OrderStatusEnum.COMPLETED]: null,
            [OrderStatusEnum.CANCELLED]: null,
        };
        return NEXT_STATUS[status];
    };

    const getOrderStatusActions = (
        status: OrderStatusEnum,
        type: OrderTypeEnum,
    ) => {
        const previousStatus = getPreviousStatus(status);
        const nextStatus = getNextStatus(status, type);

        const actions: {
            id: string;
            label: string;
            action: () => void;
            class: string;
        }[] = [];

        if (status === OrderStatusEnum.CONFIRMED) {
            actions.push({
                id: ActionEnum.STARTPREPARING,
                label: t('confirmedOrder.orderCard.actions.startPreparing'),
                action: () =>
                    updateStatus(nextStatus!, ActionEnum.STARTPREPARING),
                class: 'bg-info text-info-foreground hover:bg-info/90',
            });
        }

        if (status === OrderStatusEnum.PREPARING) {
            actions.push({
                id: ActionEnum.MARKASREADY,
                label: t('confirmedOrder.orderCard.actions.markAsReady'),
                action: () => updateStatus(nextStatus!, ActionEnum.MARKASREADY),
                class: 'bg-warning text-warning-foreground hover:bg-warning/90',
            });

            actions.push({
                id: ActionEnum.UNDO,
                label: t('confirmedOrder.orderCard.actions.undo'),
                action: () => updateStatus(previousStatus!, ActionEnum.UNDO),
                class: 'bg-secondary text-secondary-foreground hover:bg-secondary/90',
            });
        }

        if (status === OrderStatusEnum.READY) {
            if (type === OrderTypeEnum.DELIVERY) {
                actions.push({
                    id: ActionEnum.STARTDELIVERING,
                    label: t(
                        'confirmedOrder.orderCard.actions.startDelivering',
                    ),
                    action: () =>
                        updateStatus(nextStatus!, ActionEnum.STARTDELIVERING),
                    class: 'bg-info text-info-foreground hover:bg-info/90',
                });
            } else {
                actions.push({
                    id: ActionEnum.COMPLETEORDER,
                    label: t('confirmedOrder.orderCard.actions.completeOrder'),
                    action: () =>
                        updateStatus(nextStatus!, ActionEnum.COMPLETEORDER),
                    class: 'bg-success text-success-foreground hover:bg-success/90',
                });
            }

            actions.push({
                id: ActionEnum.UNDO,
                label: t('confirmedOrder.orderCard.actions.undo'),
                action: () => updateStatus(previousStatus!, ActionEnum.UNDO),
                class: 'bg-secondary text-secondary-foreground hover:bg-secondary/90',
            });
        }

        if (status === OrderStatusEnum.DELIVERING) {
            actions.push({
                id: ActionEnum.COMPLETEORDER,
                label: t('confirmedOrder.orderCard.actions.completeOrder'),
                action: () =>
                    updateStatus(nextStatus!, ActionEnum.COMPLETEORDER),
                class: 'bg-success text-success-foreground hover:bg-success/90',
            });

            actions.push({
                id: ActionEnum.UNDO,
                label: t('confirmedOrder.orderCard.actions.undo'),
                action: () => updateStatus(previousStatus!, ActionEnum.UNDO),
                class: 'bg-secondary text-secondary-foreground hover:bg-secondary/90',
            });
        }

        return actions;
    };

    return {
        getOrderStatusActions,
    };
}
