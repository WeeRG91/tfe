export default {
    title: 'Fleesch',
    table: {
        name: 'Numm',
        extraPrice: 'Zousazpräis',
        createdAt: 'Erstallt den',
        updatedAt: 'Aktualiséiert den',
        deletedAt: 'Geläscht den',
    },
    messages: {
        noMeat: 'Nach kee Fleesch',
        confirmRestore:
            'Sidd Dir sécher datt Dir dëst Fleesch restauréiere wëllt?',
        confirmDelete:
            'Sidd Dir sécher datt Dir dëst Fleesch definitiv läsche wëllt?',
        confirmMoveToBin:
            'Sidd Dir sécher datt Dir dëst Fleesch an den Dreckskuerf réckele wëllt?',
    },
    errors: {
        loadFailed: 'Méi Fleeschzorte konnten net geluede ginn.',
        binFailed: "D'Fleesch konnt net an den Dreckskuerf geréckelt ginn.",
        restoreFailed: "D'Fleesch konnt net restauréiert ginn.",
        deleteFailed: "D'Fleesch konnt net geläscht ginn.",
    },
    form: {
        title: 'Fleesch dobäisetzen',
        editTitle: 'Fleesch änneren: {name}',
        breadcrumbs: {
            meats: 'Fleesch',
            create: 'Uleeën',
            edit: 'Änneren',
        },
        sections: {
            information: 'Informatioun iwwer d’Fleesch',
            photos: 'Fotoen',
        },
        fields: {
            name: 'Numm',
            description: 'Beschreiwung',
            extraPrice: 'Zousazpräis',
        },
        messages: {
            created: 'D’Fleesch gouf erfollegräich erstallt.',
            edited: "D'Fleesch gouf erfollegräich aktualiséiert.",
            error: 'E Feeler ass opgetrueden. Kontrolléiert w.e.g. de Formulaire.',
        },
    },
    createModal: {
        title: 'Eng Fleeschoptioun uleeën',
        description:
            "Fëllt d'Informatiounen hei ënnen aus, fir eng nei Fleeschoptioun unzeleeën.",
        fields: {
            name: 'Numm',
            description: 'Beschreiwung',
            extraPrice: 'Zousätzleche Präis',
        },
        error: 'E Feeler ass opgetrueden. Kontrolléiert w.e.g. de Formulaire.',
    },
};
