export default {
    title: 'Rollen',
    subtitle: 'Rollen an hir Berechtigunge verwalten',
    permissions: {
        label: '{count} Berechtigungen',
        title: 'Berechtigungen',
        noPermissions: 'Keng Berechtigungen zougewisen',
        uncategorized: 'Ouni Kategorie',
    },
    dates: { updated: 'Aktualiséiert den: {date}' },
    messages: {
        loadMore: 'Méi Rolle lueden',
        noSearchResults: 'Keng Rolle fonnt, déi mat „{query}“ iwwereneestëmmen',
        confirmDelete:
            'Sidd Dir sécher, datt Dir dës Roll definitiv läsche wëllt?',
    },
    errors: {
        loadFailed: 'D’Rolle konnten net geluede ginn.',
        deleteFailed: 'D’Roll konnt net geläscht ginn.',
    },
    form: {
        createTitle: 'Roll erstellen',
        editTitle: 'Roll änneren',
        sections: {
            roleInformation: 'Informatioun iwwer d’Roll',
            permissions: 'Berechtigungen',
        },
        fields: { roleName: 'Numm vun der Roll' },
        placeholders: {
            roleName: 'Numm vun der Roll aginn (z. B. Editeur, Manager)',
        },
        descriptions: { permissions: 'Wielt d’Berechtigunge fir dës Roll aus' },
        labels: { selected: '{count} ausgewielt' },
        messages: {
            created: 'D’Roll gouf erfollegräich erstallt.',
            edited: 'D’Roll gouf erfollegräich aktualiséiert.',
            invalidInput: 'Ongülteg Donnéeën.',
            error: 'E Feeler ass opgetrueden. Kontrolléiert w.e.g. de Formulaire.',
        },
    },
};
