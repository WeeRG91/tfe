export default {
    pageTitle: 'Notifications',
    eyebrow: 'Mon compte',
    title: 'Notifications',
    description:
        "Restez informé de l'état de vos commandes et de nos offres exclusives",
    filters: {
        all: 'Toutes',
        read: 'Lues',
        unread: 'Non lues',
    },
    menu: {
        moreOptions: "Plus d'options",
        markAllAsRead: 'Tout lire',
        deleteAll: 'Tout supprimer',
    },
    confirm: {
        deleteAll: 'Voulez-vous vraiment supprimer toutes les notifications ?',
    },
    success: {
        deleted: 'Notification supprimée avec succès.',
        deletedAll: 'Toutes les notifications ont été supprimées.',
    },
    errors: {
        loadFailed: 'Impossible de charger les notifications.',
        deleteFailed: 'Impossible de supprimer la notification.',
        deleteAllFailed: 'Impossible de supprimer toutes les notifications.',
    },
    notificationItem: {
        markAsRead: 'Marquer comme lu',
        delete: 'Supprimer',
        confirmDelete: 'Voulez-vous vraiment supprimer cette notification ?',
    },
    stored: {
        orderConfirmed: {
            title: 'Commande confirmée',
            message: 'Votre commande n°{number} a été confirmée.',
        },
        orderReady: {
            title: 'Commande prête',
            pickup: 'Votre commande n°{number} est prête à être retirée.',
            delivery: 'Votre commande n°{number} est prête à être livrée.',
            serving: 'Votre commande n°{number} est prête à être servie.',
        },
        orderDelivering: {
            title: 'Commande en cours de livraison',
            message: 'Votre commande n°{number} est en cours de livraison.',
        },
        orderCompleted: {
            title: 'Commande terminée',
            message: 'Votre commande n°{number} est terminée.',
        },
        orderCancelled: {
            title: 'Commande annulée',
            message: 'Votre commande n°{number} a été annulée.',
        },
    },
    notificationDrawer: {
        title: 'Notifications',
        new: 'Nouveau',
        menu: {
            seeAll: 'Voir tout',
            seeAllNotifications: 'Voir toutes les notifications',
            markAllAsRead: 'Tout lire',
            deleteAll: 'Tout supprimer',
        },
        empty: {
            title: 'Aucune notification',
            description:
                'Nous vous informerons dès qu’une nouvelle notification sera disponible',
        },
        confirm: {
            delete: 'Voulez-vous vraiment supprimer cette notification ?',
            deleteAll:
                'Voulez-vous vraiment supprimer toutes les notifications ?',
        },
        actions: {
            markAsRead: 'Marquer comme lu',
            delete: 'Supprimer',
        },
    },
};
