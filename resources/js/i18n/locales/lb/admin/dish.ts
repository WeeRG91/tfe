export default {
    title: 'Platen',
    categories: {
        all: 'All',
        appetizer: 'Entrée',
        mainCourse: 'Haaptplat',
        soup: 'Zopp',
        noodles: 'Nuddelen',
        dessert: 'Dessert',
        vegetarian: 'Vegetaresch',
    },
    spicyLevel: {
        noSpicy: 'Net schaarf',
        mild: 'Mëll',
        spicy: 'Schaarf',
        hot: 'Ganz schaarf',
    },
    filters: {
        all: 'All',
        available: 'Disponibel',
        unavailable: 'Net disponibel',
        deleted: 'Geläscht',
        activeFilters: 'Aktiv Filter:',
        clearAll: 'Alles läschen',
        allCategories: 'All Kategorien',
        searchPlaceholder: 'Platen sichen...',
    },
    buttons: {
        add: 'Dobäisetzen',
        edit: 'Änneren',
        delete: 'Läschen',
        restore: 'Restauréieren',
        moveToBin: 'An den Dreckskuerf',
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
    status: {
        available: 'Disponibel',
        unavailable: 'Net disponibel',
    },
    messages: {
        noDish: 'Nach keng Platen',
        confirmRestore:
            'Sidd Dir sécher datt Dir dëse Plat restauréiere wëllt?',
        confirmDelete:
            'Sidd Dir sécher datt Dir dëse Plat definitiv läsche wëllt?',
        confirmBin:
            'Sidd Dir sécher datt Dir dëse Plat an den Dreckskuerf setze wëllt?',
        confirmAvailable:
            'Sidd Dir sécher datt Dir dëse Plat als disponibel markéiere wëllt?',
        confirmUnavailable:
            'Sidd Dir sécher datt Dir dëse Plat als net disponibel markéiere wëllt?',
    },
    errors: {
        loadFailed: "D'Platen konnten net geluede ginn.",
        availabilityFailed: "D'Disponibilitéit konnt net geännert ginn.",
        binFailed: 'De Plat konnt net an den Dreckskuerf gesat ginn.',
        restoreFailed: 'De Plat konnt net restauréiert ginn.',
        deleteFailed: 'De Plat konnt net geläscht ginn.',
    },
    create: {
        title: 'E Plat uleeën',
        breadcrumbs: {
            dishes: 'Platen',
            create: 'Uleeën',
        },
        sections: {
            dishInformation: 'Informatiounen zum Plat',
            photos: 'Fotoen',
        },
        fields: {
            name: 'Numm',
            description: 'Beschreiwung',
            price: 'Präis (€)',
            category: 'Kategorie',
            ingredients: 'Zutaten',
            meatOptions: 'Fleeschzorten',
            defaultSpicyLevel: 'Wielt de Standard-Schäerftgrad fir dëse Plat',
        },
        buttons: {
            create: 'Uleeën',
            cancel: 'Ofbriechen',
        },
        messages: {
            created: 'De Plat gouf erfollegräich ugeluecht.',
            error: 'E Feeler ass opgetrueden. Kontrolléiert w.e.g. de Formulaire.',
        },
    },
};
