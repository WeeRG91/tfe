export default {
    title: 'Allergènes',
    table: {
        name: 'Nom',
        createdAt: 'Créé le',
        updatedAt: 'Mis à jour le',
        deletedAt: 'Supprimé le',
    },
    messages: {
        noAllergen: 'Aucun allergène pour le moment',
        confirmRestore: 'Êtes-vous sûr de vouloir restaurer cet allergène ?',
        confirmDelete:
            'Êtes-vous sûr de vouloir supprimer définitivement cet allergène ?',
        confirmMoveToBin:
            'Êtes-vous sûr de vouloir déplacer cet allergène dans la corbeille ?',
    },
    errors: {
        loadFailed: 'Impossible de charger davantage d’allergènes.',
        binFailed: 'Impossible de déplacer l’allergène dans la corbeille.',
        restoreFailed: 'Impossible de restaurer l’allergène.',
        deleteFailed: 'Impossible de supprimer l’allergène.',
    },
    form: {
        title: 'Créer un allergène',
        editTitle: "Editer l'allergène : {name}",
        breadcrumbs: {
            allergens: 'Allergènes',
            create: 'Créer',
            edit: 'Editer',
        },
        sections: {
            information: "Informations sur l'allergène",
            photos: 'Photos',
        },
        fields: {
            name: 'Nom',
            description: 'Description',
            ingredients: 'Ingrédients',
        },
        messages: {
            created: "L'allergène a été créé avec succès.",
            edited: "L'allergène a été mis à jour avec succès.",
            error: 'Une erreur est survenue. Veuillez vérifier le formulaire.',
        },
    },
};
