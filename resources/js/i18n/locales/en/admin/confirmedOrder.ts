export default {
    title: 'Confirmed Orders',
    subtitle: 'Manage and track orders by status',
    columns: {
        confirmed: 'Confirmed (Waiting)',
        preparing: 'Preparing',
        ready: 'Ready',
    },
    buttons: {
        completed: 'Completed ({count})',
        cancelled: 'Cancelled ({count})',
        refresh: 'Refresh',
    },
    emptyStates: {
        confirmed: 'No confirmed orders',
        preparing: 'No orders being prepared',
        ready: 'No orders ready for delivery',
        completed: 'No completed orders',
        cancelled: 'No cancelled orders',
    },
    modals: {
        completedTitle: 'Completed Orders',
        cancelledTitle: 'Cancelled Orders',
        orderCount: '{count} order | {count} orders',
    },
    messages: { updated: 'Order status updated successfully!' },
    errors: { updateFailed: 'Failed to update order status.' },
};
