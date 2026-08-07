import { NotificationType, NotificationTypeEnum } from '@/types/notification';
import { useI18n } from 'vue-i18n';

export function useNotificationTranslator() {
    const { t } = useI18n();

    const getNotificationTitle = (notification: NotificationType): string => {
        switch (notification.type) {
            case NotificationTypeEnum.ORDER_CONFIRMED:
                return t('notification.stored.orderConfirmed.title');
            case NotificationTypeEnum.ORDER_READY:
                return t('notification.stored.orderReady.title');
            case NotificationTypeEnum.ORDER_DELIVERING:
                return t('notification.stored.orderDelivering.title');
            case NotificationTypeEnum.ORDER_COMPLETED:
                return t('notification.stored.orderCompleted.title');
            case NotificationTypeEnum.ORDER_CANCELLED:
                return t('notification.stored.orderCancelled.title');
            default:
                return notification.title;
        }
    };

    const getNotificationMessage = (notification: NotificationType): string => {
        const number = notification.data.order_number ?? '';

        switch (notification.type) {
            case NotificationTypeEnum.ORDER_CONFIRMED:
                return t('notification.stored.orderConfirmed.message', {
                    number,
                });
            case NotificationTypeEnum.ORDER_READY:
                if (notification.data.order_type === 'takeaway') {
                    return t('notification.stored.orderReady.pickup', {
                        number,
                    });
                }

                if (notification.data.order_type === 'delivery') {
                    return t('notification.stored.orderReady.delivery', {
                        number,
                    });
                }

                return t('notification.stored.orderReady.serving', {
                    number,
                });
            case NotificationTypeEnum.ORDER_DELIVERING:
                return t('notification.stored.orderDelivering.message', {
                    number,
                });
            case NotificationTypeEnum.ORDER_COMPLETED:
                return t('notification.stored.orderCompleted.message', {
                    number,
                });
            case NotificationTypeEnum.ORDER_CANCELLED:
                return t('notification.stored.orderCancelled.message', {
                    number,
                });
            default:
                return notification.message;
        }
    };

    return {
        getNotificationTitle,
        getNotificationMessage,
    };
}
