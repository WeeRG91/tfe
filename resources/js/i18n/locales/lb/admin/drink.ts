export default {
    title: 'Gedrénks',
    categories: {
        all: 'All',
        softDrink: 'Softdrink',
        hotDrink: 'Waarmt Gedrénks',
        smoothie: 'Smoothie',
        beer: 'Béier',
        wine: 'Wäin',
        cocktail: 'Cocktail',
        mocktail: 'Cocktail ouni Alkohol',
    },
    table: {
        name: 'Numm',
        category: 'Kategorie',
        price: 'Präis',
        availability: 'Disponibilitéit',
        createdAt: 'Erstallt den',
        updatedAt: 'Aktualiséiert den',
        deletedAt: 'Geläscht den',
    },
    messages: {
        noDrink: 'Nach keng Gedrénks',
        confirmRestore:
            'Sidd Dir sécher datt Dir dëst Gedrénks restauréiere wëllt?',
        confirmDelete:
            'Sidd Dir sécher datt Dir dëst Gedrénks definitiv läsche wëllt?',
        confirmMoveToBin:
            'Sidd Dir sécher datt Dir dëst Gedrénks an den Dreckskuerf setze wëllt?',
        confirmAvailable:
            'Sidd Dir sécher datt Dir dëst Gedrénks als disponibel markéiere wëllt?',
        confirmUnavailable:
            'Sidd Dir sécher datt Dir dëst Gedrénks als net disponibel markéiere wëllt?',
    },
    errors: {
        loadFailed: "D'Gedrénks konnten net geluede ginn.",
        availabilityFailed: "D'Disponibilitéit konnt net geännert ginn.",
        binFailed: "D'Gedrénks konnt net an den Dreckskuerf gesat ginn.",
        restoreFailed: "D'Gedrénks konnt net restauréiert ginn.",
        deleteFailed: "D'Gedrénks konnt net geläscht ginn.",
    },
    form: {
        title: 'E Gedrénks uleeën',
        editTitle: 'Gedrénks änneren: {name}',
        breadcrumbs: {
            drinks: 'Gedrénks',
            create: 'Uleeën',
            edit: 'Änneren',
        },
        sections: {
            information: 'Informatiounen zum Gedrénks',
            photos: 'Fotoen',
        },
        fields: {
            name: 'Numm',
            description: 'Beschreiwung',
            price: 'Präis (€)',
            category: 'Kategorie',
        },
        messages: {
            created: "D'Gedrénks gouf erfollegräich ugeluecht.",
            edited: "D'Gedrénks gouf erfollegräich geännert.",
            error: 'E Feeler ass opgetrueden. Kontrolléiert w.e.g. de Formulaire.',
        },
    },
};
