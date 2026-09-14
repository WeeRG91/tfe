export default {
    title: 'Zutat',
    allAllergens: 'All Allergènen',
    table: {
        name: 'Numm',
        allergen: 'Allergèn',
        createdAt: 'Erstallt den',
        updatedAt: 'Aktualiséiert den',
        deletedAt: 'Geläscht den',
    },
    messages: {
        noIngredient: 'Nach keng Zutaten',
        confirmRestore:
            'Sidd Dir sécher datt Dir dës Zutat restauréiere wëllt?',
        confirmDelete:
            'Sidd Dir sécher datt Dir dës Zutat definitiv läsche wëllt?',
        confirmMoveToBin:
            'Sidd Dir sécher datt Dir dës Zutat an den Dreckskuerf réckele wëllt?',
    },
    errors: {
        loadFailed: 'Méi Zutaten konnten net geluede ginn.',
        binFailed: "D'Zutat konnt net an den Dreckskuerf geréckelt ginn.",
        restoreFailed: "D'Zutat konnt net restauréiert ginn.",
        deleteFailed: "D'Zutat konnt net geläscht ginn.",
    },
    form: {
        title: 'Zutat erstellen',
        editTitle: 'Zutat änneren: {name}',
        breadcrumbs: {
            ingredients: 'Zutaten',
            create: 'Uleeën',
            edit: 'Änneren',
        },
        sections: {
            information: "Informatioun iwwer d'Zutat",
            photos: 'Fotoen',
        },
        fields: {
            name: 'Numm',
            description: 'Beschreiwung',
            allergen: 'Allergèn',
        },
        messages: {
            created: "D'Zutat gouf erfollegräich erstallt.",
            updated: "D'Zutat gouf erfollegräich aktualiséiert.",
            error: 'E Feeler ass opgetrueden. Kontrolléiert w.e.g. de Formulaire.',
        },
    },
    createModal: {
        title: 'En Zutat uleeën',
        description:
            "Fëllt d'Informatiounen hei ënnen aus, fir eng nei Zutat unzeleeën.",
        fields: {
            name: 'Numm',
            description: 'Beschreiwung',
            allergen: 'Allergen',
        },
        error: 'E Feeler ass opgetrueden. Kontrolléiert w.e.g. de Formulaire.',
    },
};
