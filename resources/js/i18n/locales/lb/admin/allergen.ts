export default {
    title: 'Allergènen',
    table: {
        name: 'Numm',
        createdAt: 'Erstallt den',
        updatedAt: 'Aktualiséiert den',
        deletedAt: 'Geläscht den',
    },
    messages: {
        noAllergen: 'Nach keng Allergènen',
        confirmRestore:
            'Sidd Dir sécher, datt Dir dësen Allergèn restauréiere wëllt?',
        confirmDelete:
            'Sidd Dir sécher, datt Dir dësen Allergèn definitiv läsche wëllt?',
        confirmMoveToBin:
            'Sidd Dir sécher, datt Dir dësen Allergèn an den Dreckskuerf réckele wëllt?',
    },
    errors: {
        loadFailed: 'Méi Allergènen konnten net geluede ginn.',
        binFailed: 'Den Allergèn konnt net an den Dreckskuerf geréckelt ginn.',
        restoreFailed: 'Den Allergèn konnt net restauréiert ginn.',
        deleteFailed: 'Den Allergèn konnt net geläscht ginn.',
    },
    form: {
        title: 'Allergèn Uleeën',
        editTitle: 'Allergèn änneren: {name}',
        breadcrumbs: {
            allergens: 'Allergènen',
            create: 'Uleeën',
            edit: 'Änneren',
        },
        sections: {
            information: 'Informatioun iwwer den Allergèn',
            photos: 'Fotoen',
        },
        fields: {
            name: 'Numm',
            description: 'Beschreiwung',
            ingredients: 'Zutaten',
        },
        messages: {
            created: 'Den Allergèn gouf erfollegräich erstallt.',
            edited: 'Den Allergèn gouf erfollegräich aktualiséiert.',
            error: 'E Feeler ass opgetrueden. Kontrolléiert w.e.g. de Formulaire.',
        },
    },
};
