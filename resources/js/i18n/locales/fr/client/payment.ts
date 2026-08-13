export default {
    title: 'Paiement',
    secureCheckout: 'Paiement sécurisé',
    completePayment: 'Finaliser le paiement',
    stripePowered: 'Paiement sécurisé via Stripe',

    order: {
        orderNumber: 'Numéro de commande',
        status: 'Statut',
        orderDetails: 'Détails de la commande',
        orderType: 'Type de commande',
        table: 'Table',
        pickup: 'Retrait',
        customer: 'Client',
        deliveryAddress: 'Adresse de livraison',
        deliveryToBms: 'Livraison à BMS',
        orderItems: 'Articles de la commande',
    },

    item: {
        spicyLevel: 'Niveau de piquant',
        noSpicy: 'Non épicé',
        mild: 'Doux',
        spicy: 'Épicé',
        hot: 'Très épicé',
        meat: 'Viande',
        without: 'Sans',
        notes: 'Notes',
    },

    summary: {
        paymentSummary: 'Récapitulatif du paiement',
        vat: 'TVA {rate}%',
        totalVat: 'TVA totale',
        subtotal: 'Sous-total',
        deliveryFee: 'Frais de livraison',
        discount: 'Réduction',
        totalAmount: 'Montant total',
    },

    payment: {
        paymentDetails: 'Détails du paiement',
        paymentUnsuccessful: 'Échec du paiement',
        notCharged:
            "Votre commande n'a pas été débitée. Vous pouvez corriger vos informations de paiement et réessayer.",
        loadingPaymentForm: 'Chargement du formulaire de paiement…',
        pay: 'Payer €{amount}',
        termsAgreement:
            'En effectuant le paiement, vous acceptez nos Conditions générales',
    },

    errors: {
        initializationFailed: "Le paiement n'a pas pu être initialisé.",
        stripeLoadFailed: "Stripe n'a pas pu être chargé.",
        unableToInitialize:
            "Impossible d'initialiser le paiement. Veuillez réessayer.",
        paymentFailed:
            "Votre paiement n'a pas pu être effectué. Veuillez réessayer.",
        unexpected: 'Une erreur inattendue est survenue. Veuillez réessayer.',
    },

    common: {
        notAvailable: 'N/D',
    },
};
