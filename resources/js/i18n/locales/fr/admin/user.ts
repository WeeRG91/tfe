export default {
    title: 'Utilisateurs',
    subtitle: 'Gérer les utilisateurs et leurs rôles',
    messages: {
        noUsers: 'Aucun utilisateur trouvé',
        noSearchResults: 'Aucun utilisateur correspondant à « {query} »',
        joined: 'Inscrit le : {date}',
        loadMore: 'Charger plus d’utilisateurs',
        confirmInactivate:
            'Êtes-vous sûr de vouloir désactiver cet utilisateur ?',
        confirmReactivate:
            'Êtes-vous sûr de vouloir réactiver cet utilisateur ?',
        confirmDelete:
            'Êtes-vous sûr de vouloir supprimer définitivement cet utilisateur ?',
    },
    errors: {
        loadFailed: 'Impossible de charger les utilisateurs.',
        inactivateFailed: 'Impossible de désactiver l’utilisateur.',
        reactivateFailed: 'Impossible de réactiver l’utilisateur.',
        deleteFailed: 'Impossible de supprimer l’utilisateur.',
    },
    details: {
        joined: 'Inscrit le {date}',
        stats: {
            roles: 'Rôles',
            totalPoints: 'Total des points',
            orders: 'Commandes',
            permissions: 'Permissions',
        },
        permissions: {
            title: 'Rôles et permissions',
            assignedRoles: 'Rôles attribués',
            allPermissions: 'Toutes les permissions',
            uncategorized: 'Non catégorisé',
            extra: 'Supplémentaire',
            both: 'Les deux',
            noPermissions: 'Aucune permission attribuée',
            noRoles: 'Aucun rôle attribué à cet utilisateur',
        },
        orders: {
            title: 'Historique des commandes',
            noOrders: 'Aucune commande trouvée pour cet utilisateur',
        },
        loyaltyPoints: {
            title: 'Historique des points de fidélité',
            noHistory: 'Aucun historique de points de fidélité',
        },
        dates: {
            createdAt: 'Créé le :',
            lastUpdated: 'Dernière mise à jour :',
        },
        messages: {
            confirmInactivate:
                'Êtes-vous sûr de vouloir désactiver cet utilisateur ?',
            confirmReactivate:
                'Êtes-vous sûr de vouloir réactiver cet utilisateur ?',
            confirmDelete:
                'Êtes-vous sûr de vouloir supprimer définitivement cet utilisateur ?',
        },
        errors: {
            inactivateFailed: "Impossible de désactiver l'utilisateur.",
            reactivateFailed: "Impossible de réactiver l'utilisateur.",
            deleteFailed: "Impossible de supprimer l'utilisateur.",
        },
    },
    form: {
        editTitle: "Modifier l'utilisateur",
        sections: {
            userInformation: "Informations de l'utilisateur",
            roleAssignment: 'Attribution du rôle',
            permissions: 'Permissions',
        },
        fields: {
            fullName: 'Nom complet',
            emailAddress: 'Adresse e-mail',
            password: 'Mot de passe',
            confirmPassword: 'Confirmer le mot de passe',
            role: 'Rôle',
        },
        placeholders: {
            fullName: 'Saisissez le nom complet',
            emailAddress: "Saisissez l'adresse e-mail",
            password: 'Saisissez le mot de passe',
            confirmPassword: 'Confirmez le mot de passe',
            selectRole: 'Sélectionnez un rôle',
        },
        descriptions: {
            roleAssignment:
                'Sélectionnez un rôle pour cet utilisateur. Les permissions seront automatiquement attribuées en fonction du rôle.',
            permissions: 'Personnalisez les permissions de cet utilisateur.',
        },
        labels: { selected: '{count} sélectionnés' },
        messages: {
            updated: "L'utilisateur a été mis à jour avec succès.",
            invalidInput: 'Données invalides.',
            error:
                'Une erreur est survenue. Veuillez vérifier le formulaire.',
        },
    },
};
