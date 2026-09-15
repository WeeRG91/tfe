export default {
    title: 'Dashboard',
    period: 'Period',
    periods: {
        today: 'Today',
        last7: 'Last 7 days, including today',
        last30: 'Last 30 days, including today',
        last90: 'Last 90 days, including today',
    },
    periodOptions: {
        today: 'Today',
        last7: 'Last 7 days',
        last30: 'Last 30 days',
        last90: 'Last 90 days',
    },
    cards: {
        completedOrders: 'Completed orders',
        paidSales: 'Paid completed sales',
        averageOrderValue: 'Average paid order value',
        cancellationRate: 'Cancellation rate',
        preparationTime: 'Average preparation time',
        returningCustomers: 'Returning paying customers',
    },
    details: {
        cancellation: '{cancelled} of {total} orders placed · {period}',
        preparationSample: 'Based on {count} completed orders · {period}',
        noTimedOrders: 'No timed completed orders · {period}',
        returningCustomers: '{returning} of {total} customers · {period}',
        noPayingCustomers: 'No paid completed customers · {period}',
    },
    units: {
        minutes: 'min',
    },
    charts: {
        dailySales: {
            title: 'Daily paid completed sales',
            seriesName: 'Paid completed sales',
            detail: 'Including VAT',
        },
        topDishes: {
            title: 'Top-selling dishes',
            tooltip: '{count} units sold',
            series: 'Units sold',
            detail: 'Ranked by units sold',
            empty: 'No paid completed dish orders in this period.',
        },
        ordersByType: {
            title: 'Orders placed by type',
            tooltip: '{type}: {count} orders ({percent}%)',
            seriesName: 'Orders placed',
            detail: 'Includes cancelled orders',
            empty: 'No orders placed in this period.',
        },
        ordersByHour: {
            title: 'Orders placed by hour',
            tooltip: '{count} orders',
            seriesName: 'Orders placed',
            detail: 'Restaurant local time',
            empty: 'No orders placed in this period.',
        },
    },
};
