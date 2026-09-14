export default {
    title: 'Benotzer',
    subtitle: 'Benotzer an hir Rollen verwalten',
    messages: {
        noUsers: 'Keng Benotzer fonnt',
        noSearchResults:
            'Keng Benotzer fonnt, déi mat „{query}“ iwwereneestëmmen',
        joined: 'Bäigetrueden den: {date}',
        loadMore: 'Méi Benotzer lueden',
        confirmInactivate:
            'Sidd Dir sécher, datt Dir dëse Benotzer deaktivéiere wëllt?',
        confirmReactivate:
            'Sidd Dir sécher, datt Dir dëse Benotzer reaktivéiere wëllt?',
        confirmDelete:
            'Sidd Dir sécher, datt Dir dëse Benotzer definitiv läsche wëllt?',
    },
    errors: {
        loadFailed: 'D’Benotzer konnten net geluede ginn.',
        inactivateFailed: 'De Benotzer konnt net deaktivéiert ginn.',
        reactivateFailed: 'De Benotzer konnt net reaktivéiert ginn.',
        deleteFailed: 'De Benotzer konnt net geläscht ginn.',
    },
    details: {
        joined: 'Bäigetrueden den {date}',
        stats: {
            roles: 'Rollen',
            totalPoints: 'Punkten am Ganzen',
            orders: 'Bestellungen',
            permissions: 'Berechtigungen',
        },
        permissions: {
            title: 'Rollen a Berechtigungen',
            assignedRoles: 'Zougewisen Rollen',
            allPermissions: 'All Berechtigungen',
            uncategorized: 'Ouni Kategorie',
            extra: 'Zousätzlech',
            both: 'Béid',
            noPermissions: 'Keng Berechtigungen zougewisen',
            noRoles: 'Dësem Benotzer si keng Rollen zougewisen',
        },
        orders: {
            title: 'Bestellungsverlaf',
            noOrders: 'Keng Bestellunge fir dëse Benotzer fonnt',
        },
        loyaltyPoints: {
            title: 'Verlaf vun den Treiheetspunkten',
            noHistory: 'Kee Verlaf vun den Treiheetspunkten',
        },
        dates: {
            createdAt: 'Erstallt den:',
            lastUpdated: 'Lescht aktualiséiert:',
        },
        messages: {
            confirmInactivate:
                'Sidd Dir sécher, datt Dir dëse Benotzer deaktivéiere wëllt?',
            confirmReactivate:
                'Sidd Dir sécher, datt Dir dëse Benotzer reaktivéiere wëllt?',
            confirmDelete:
                'Sidd Dir sécher, datt Dir dëse Benotzer definitiv läsche wëllt?',
        },
        errors: {
            inactivateFailed: 'De Benotzer konnt net deaktivéiert ginn.',
            reactivateFailed: 'De Benotzer konnt net reaktivéiert ginn.',
            deleteFailed: 'De Benotzer konnt net geläscht ginn.',
        },
    },
    form: {
        editTitle: 'Benotzer änneren',
        sections: {
            userInformation: 'Benotzerinformatioun',
            roleAssignment: 'Roll zouweisen',
            permissions: 'Berechtigungen',
        },
        fields: {
            fullName: 'Vollen Numm',
            emailAddress: 'E-Mail-Adress',
            password: 'Passwuert',
            confirmPassword: 'Passwuert confirméieren',
            role: 'Roll',
        },
        placeholders: {
            fullName: 'Vollen Numm aginn',
            emailAddress: 'E-Mail-Adress aginn',
            password: 'Passwuert aginn',
            confirmPassword: 'Passwuert confirméieren',
            selectRole: 'Eng Roll auswielen',
        },
        descriptions: {
            roleAssignment:
                'Wielt eng Roll fir dëse Benotzer. D’Berechtigunge ginn automatesch op Basis vun der Roll zougewisen.',
            permissions: 'D’Berechtigunge fir dëse Benotzer upassen.',
        },
        labels: { selected: '{count} ausgewielt' },
        messages: {
            updated: 'De Benotzer gouf erfollegräich aktualiséiert.',
            invalidInput: 'Ongülteg Donnéeën.',
            error: 'E Feeler ass opgetrueden. Kontrolléiert w.e.g. de Formulaire.',
        },
    },
};
