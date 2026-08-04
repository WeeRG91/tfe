export default {
    title: 'Viandes',
    table: {
        name: 'Nom',
        extraPrice: 'Supplément',
        createdAt: 'Créé le',
        updatedAt: 'Mis à jour le',
        deletedAt: 'Supprimé le',
    },
    messages: {
        noMeat: 'Aucune viande pour le moment',
        confirmRestore: 'Êtes-vous sûr de vouloir restaurer cette viande ?',
        confirmDelete:
            'Êtes-vous sûr de vouloir supprimer définitivement cette viande ?',
        confirmMoveToBin:
            'Êtes-vous sûr de vouloir déplacer cette viande dans la corbeille ?',
    },
    errors: {
        loadFailed: 'Impossible de charger davantage de viandes.',
        binFailed: 'Impossible de déplacer la viande dans la corbeille.',
        restoreFailed: 'Impossible de restaurer la viande.',
        deleteFailed: 'Impossible de supprimer la viande.',
    },
    form: {
        title: 'Créer une viande',
        editTitle: 'Editer la viande : {name}',
        breadcrumbs: {
            meats: 'Viandes',
            create: 'Créer',
            edit: 'Editer',
        },
        sections: {
            information: 'Informations sur la viande',
            photos: 'Photos',
        },
        fields: {
            name: 'Nom',
            description: 'Description',
            extraPrice: 'Supplément',
        },
        messages: {
            created: 'La viande a été créée avec succès.',
            edited: 'La viande a été mise à jour avec succès.',
            error: 'Une erreur est survenue. Veuillez vérifier le formulaire.',
        },
    },
    createModal: {
        title: 'Créer une option de viande',
        description:
            'Remplissez les informations ci-dessous pour créer une nouvelle option de viande.',
        fields: {
            name: 'Nom',
            description: 'Description',
            extraPrice: 'Prix supplémentaire',
        },
        error: 'Une erreur est survenue. Veuillez vérifier le formulaire.',
    },
};
