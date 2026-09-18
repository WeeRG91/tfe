export default {
    title: 'Payment',
    secureCheckout: 'Secure Checkout',
    completePayment: 'Complete Payment',
    stripePowered: 'Secure payment powered by Stripe',
    order: {
        orderNumber: 'Order Number',
        status: 'Status',
        orderDetails: 'Order Details',
        orderType: 'Order Type',
        table: 'Table',
        pickup: 'Pickup',
        customer: 'Customer',
        deliveryAddress: 'Delivery Address',
        orderItems: 'Order Items',
        deliveryCompany: 'Delivery to company',
        deliveryDate: 'Delivery date',
    },
    item: {
        spicyLevel: 'Spicy level',
        meat: 'Meat',
        without: 'Without',
        notes: 'Notes',
    },
    summary: {
        paymentSummary: 'Payment Summary',
        vat: 'VAT {rate}%',
        totalVat: 'Total VAT',
        subtotal: 'Subtotal',
        deliveryFee: 'Delivery fee',
        discount: 'Discount',
        totalAmount: 'Total Amount',
    },
    payment: {
        paymentDetails: 'Payment Details',
        paymentUnsuccessful: 'Payment unsuccessful',
        notCharged:
            'Your order has not been charged. You can correct your payment details and try again.',
        loadingPaymentForm: 'Loading payment form…',
        pay: 'Pay €{amount}',
        termsAgreement:
            'By completing payment, you agree to our Terms of Service',
    },
    errors: {
        initializationFailed: 'Payment could not be initialized.',
        stripeLoadFailed: 'Stripe could not be loaded.',
        unableToInitialize: 'Unable to initialize payment. Please try again.',
        paymentFailed: 'Your payment could not be completed. Please try again.',
        unexpected: 'An unexpected error occurred. Please try again.',
    },
    common: {
        notAvailable: 'N/A',
    },
};
