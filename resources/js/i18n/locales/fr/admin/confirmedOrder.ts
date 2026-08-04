export default {
    title: 'Commandes confirmées',
    subtitle: 'Gérer et suivre les commandes selon leur statut',
    columns: {
        confirmed: 'Confirmées (en attente)',
        preparing: 'En préparation',
        ready: 'Prêtes',
    },
    buttons: {
        completed: 'Terminées ({count})',
        cancelled: 'Annulées ({count})',
        refresh: 'Actualiser',
    },
    emptyStates: {
        confirmed: 'Aucune commande confirmée',
        preparing: 'Aucune commande en préparation',
        ready: 'Aucune commande prête pour la livraison',
        completed: 'Aucune commande terminée',
        cancelled: 'Aucune commande annulée',
    },
    modals: {
        completedTitle: 'Commandes terminées',
        cancelledTitle: 'Commandes annulées',
        orderCount: '{count} commande | {count} commandes',
    },
    messages: {
        updated: 'Le statut de la commande a été mis à jour avec succès.',
    },
    errors: {
        updateFailed:
            'Impossible de mettre à jour le statut de la commande.',
    },
};
