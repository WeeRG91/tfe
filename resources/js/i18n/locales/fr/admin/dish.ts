export default {
    title: 'Plats',
    categories: {
        all: 'Tous',
        appetizer: 'Entrée',
        mainCourse: 'Plat principal',
        soup: 'Soupe',
        noodles: 'Nouilles',
        dessert: 'Dessert',
        vegetarian: 'Végétarien',
    },
    spicyLevel: {
        noSpicy: 'Non épicé',
        mild: 'Doux',
        spicy: 'Épicé',
        hot: 'Très épicé',
    },
    table: {
        name: 'Nom',
        category: 'Catégorie',
        price: 'Prix',
        availability: 'Disponibilité',
        createdAt: 'Créé le',
        updatedAt: 'Mis à jour le',
        deletedAt: 'Supprimé le',
    },
    messages: {
        noDish: 'Aucun plat pour le moment',
        confirmRestore: 'Voulez-vous vraiment restaurer ce plat ?',
        confirmDelete:
            'Voulez-vous vraiment supprimer définitivement ce plat ?',
        confirmBin: 'Voulez-vous vraiment mettre ce plat à la corbeille ?',
        confirmAvailable: 'Voulez-vous vraiment rendre ce plat disponible ?',
        confirmUnavailable:
            'Voulez-vous vraiment rendre ce plat indisponible ?',
    },
    errors: {
        loadFailed: 'Impossible de charger davantage de plats.',
        availabilityFailed: 'Impossible de modifier la disponibilité du plat.',
        binFailed: 'Impossible de déplacer le plat dans la corbeille.',
        restoreFailed: 'Impossible de restaurer le plat.',
        deleteFailed: 'Impossible de supprimer le plat.',
    },
    form: {
        title: 'Créer un plat',
        breadcrumbs: {
            dishes: 'Plats',
            create: 'Créer',
            edit: 'Editer',
        },
        sections: {
            information: 'Informations sur le plat',
            photos: 'Photos',
        },
        fields: {
            name: 'Nom',
            description: 'Description',
            price: 'Prix (€)',
            category: 'Catégorie',
            ingredients: 'Ingrédients',
            meatOptions: 'Types de viande',
            defaultSpicyLevel:
                'Sélectionnez le niveau de piquant par défaut de ce plat',
        },
        messages: {
            created: 'Le plat a été créé avec succès.',
            edited: 'Le plat a été mis à jour avec succès.',
            error: 'Une erreur est survenue. Veuillez vérifier le formulaire.',
        },
    },
};
