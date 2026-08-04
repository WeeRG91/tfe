export default {
    title: 'Rôles',
    subtitle: 'Gérer les rôles et leurs permissions',
    permissions: {
        label: '{count} permissions',
        title: 'Permissions',
        noPermissions: 'Aucune permission attribuée',
        uncategorized: 'Non catégorisé',
    },
    dates: { updated: 'Mis à jour le : {date}' },
    messages: {
        loadMore: 'Charger plus de rôles',
        noSearchResults: 'Aucun rôle correspondant à « {query} »',
        confirmDelete:
            'Êtes-vous sûr de vouloir supprimer définitivement ce rôle ?',
    },
    errors: {
        loadFailed: 'Impossible de charger les rôles.',
        deleteFailed: 'Impossible de supprimer le rôle.',
    },
    form: {
        createTitle: 'Créer un rôle',
        editTitle: 'Modifier le rôle',
        sections: {
            roleInformation: 'Informations sur le rôle',
            permissions: 'Permissions',
        },
        fields: { roleName: 'Nom du rôle' },
        placeholders: {
            roleName: 'Saisissez le nom du rôle (par ex. Éditeur, Responsable)',
        },
        descriptions: {
            permissions: 'Sélectionnez les permissions pour ce rôle',
        },
        labels: { selected: '{count} sélectionnées' },
        messages: {
            created: 'Le rôle a été créé avec succès.',
            edited: 'Le rôle a été mis à jour avec succès.',
            invalidInput: 'Données invalides.',
            error: 'Une erreur est survenue. Veuillez vérifier le formulaire.',
        },
    },
};
