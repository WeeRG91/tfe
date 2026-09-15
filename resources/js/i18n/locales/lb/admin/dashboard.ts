export default {
    title: 'Dashboard',
    period: 'Period',
    periods: {
        today: 'Haut',
        last7: 'Déi lescht 7 Deeg, haut abegraff',
        last30: 'Déi lescht 30 Deeg, haut abegraff',
        last90: 'Déi lescht 90 Deeg, haut abegraff',
    },
    periodOptions: {
        today: 'Haut',
        last7: 'Déi lescht 7 Deeg',
        last30: 'Déi lescht 30 Deeg',
        last90: 'Déi lescht 90 Deeg',
    },
    cards: {
        completedOrders: 'Ofgeschloss Bestellungen',
        paidSales: 'Bezuelt an ofgeschloss Verkaf',
        averageOrderValue: 'Duerchschnëttleche Bestellwäert',
        cancellationRate: 'Annulatiounstaux',
        preparationTime: 'Duerchschnëttlech Virbereedungszäit',
        returningCustomers: 'Widderhuelend bezuelend Clienten',
    },
    details: {
        cancellation: '{cancelled} vu {total} Bestellungen · {period}',
        preparationSample:
            'Baséiert op {count} ofgeschlossene Bestellungen · {period}',
        noTimedOrders:
            'Keng ofgeschloss Bestellunge mat Zäitmiessung · {period}',
        returningCustomers: '{returning} vu {total} Clienten · {period}',
        noPayingCustomers:
            'Keng Cliente mat bezuelten an ofgeschlossene Bestellungen · {period}',
    },
    units: {
        minutes: 'Min.',
    },
    charts: {
        dailySales: {
            title: 'Deeglech bezuelt an ofgeschloss Verkaf',
            seriesName: 'Bezuelt an ofgeschloss Verkaf',
            detail: 'TVA abegraff',
        },
        topDishes: {
            title: 'Meeschtverkaafte Platen',
            tooltip: '{count} Unitéite verkaaft',
            series: 'Verkaaften Unitéiten',
            detail: 'No verkaaften Unitéite klasséiert',
            empty: 'Keng bezuelt an ofgeschloss Platbestellungen an dëser Period.',
        },
        ordersByType: {
            title: 'Bestellungen no Typ',
            tooltip: '{type}: {count} Bestellungen ({percent} %)',
            seriesName: 'Bestellungen',
            detail: 'Annuléiert Bestellungen abegraff',
            empty: 'Keng Bestellungen an dëser Period.',
        },
        ordersByHour: {
            title: 'Bestellungen no Auerzäit',
            tooltip: '{count} Bestellungen',
            seriesName: 'Bestellungen',
            detail: 'Lokal Zäit vum Restaurant',
            empty: 'Keng Bestellungen an dëser Period.',
        },
    },
};
