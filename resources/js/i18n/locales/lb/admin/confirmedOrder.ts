export default {
    title: 'Confirméiert Bestellungen',
    subtitle: 'Bestellungen no Status verwalten a verfollegen',
    columns: {
        confirmed: 'Confirméiert (waart)',
        preparing: 'An der Virbereedung',
        ready: 'Prett',
    },
    buttons: {
        completed: 'Ofgeschloss ({count})',
        cancelled: 'Annuléiert ({count})',
        refresh: 'Aktualiséieren',
    },
    emptyStates: {
        confirmed: 'Keng confirméiert Bestellungen',
        preparing: 'Keng Bestellungen an der Virbereedung',
        ready: 'Keng Bestellunge prett fir d’Liwwerung',
        completed: 'Keng ofgeschloss Bestellungen',
        cancelled: 'Keng annuléiert Bestellungen',
    },
    modals: {
        completedTitle: 'Ofgeschloss Bestellungen',
        cancelledTitle: 'Annuléiert Bestellungen',
        orderCount: '{count} Bestellung | {count} Bestellungen',
    },
    messages: {
        updated:
            'De Status vun der Bestellung gouf erfollegräich aktualiséiert.',
    },
    errors: {
        updateFailed:
            'De Status vun der Bestellung konnt net aktualiséiert ginn.',
    },
};
