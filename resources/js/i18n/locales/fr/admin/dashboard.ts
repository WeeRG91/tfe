export default {
    title: 'Tableau de bord',
    period: 'Période',
    periods: {
        today: 'Aujourd’hui',
        last7: '7 derniers jours, aujourd’hui inclus',
        last30: '30 derniers jours, aujourd’hui inclus',
        last90: '90 derniers jours, aujourd’hui inclus',
    },
    periodOptions: {
        today: 'Aujourd’hui',
        last7: '7 derniers jours',
        last30: '30 derniers jours',
        last90: '90 derniers jours',
    },
    cards: {
        completedOrders: 'Commandes terminées',
        paidSales: 'Ventes payées et terminées',
        averageOrderValue: 'Panier moyen payé',
        cancellationRate: 'Taux d’annulation',
        preparationTime: 'Temps moyen de préparation',
        returningCustomers: 'Clients payants récurrents',
    },
    details: {
        cancellation: '{cancelled} sur {total} commandes passées · {period}',
        preparationSample: 'Basé sur {count} commandes terminées · {period}',
        noTimedOrders: 'Aucune commande terminée chronométrée · {period}',
        returningCustomers: '{returning} sur {total} clients · {period}',
        noPayingCustomers:
            'Aucun client avec une commande payée et terminée · {period}',
    },
    units: {
        minutes: 'min',
    },
    charts: {
        dailySales: {
            title: 'Ventes quotidiennes payées et terminées',
            seriesName: 'Ventes payées et terminées',
            detail: 'TVA incluse',
        },
        topDishes: {
            title: 'Plats les plus vendus',
            tooltip: '{count} unités vendues',
            series: 'Unités vendues',
            detail: 'Classés par quantité vendue',
            empty: 'Aucune vente de plat payée et terminée pour cette période.',
        },
        ordersByType: {
            title: 'Commandes passées par type',
            tooltip: '{type} : {count} commandes ({percent} %)',
            seriesName: 'Commandes passées',
            detail: 'Inclut les commandes annulées',
            empty: 'Aucune commande passée pour cette période.',
        },
        ordersByHour: {
            title: 'Commandes passées par heure',
            tooltip: '{count} commandes',
            seriesName: 'Commandes passées',
            detail: 'Heure locale du restaurant',
            empty: 'Aucune commande passée pour cette période.',
        },
    },
};
