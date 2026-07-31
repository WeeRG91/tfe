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
    filters: {
        all: 'Tous',
        available: 'Disponibles',
        unavailable: 'Indisponibles',
        deleted: 'Supprimés',
        activeFilters: 'Filtres actifs :',
        clearAll: 'Tout effacer',
        allCategories: 'Toutes les catégories',
        searchPlaceholder: 'Rechercher un plat...',
    },
    buttons: {
        add: 'Ajouter',
        edit: 'Modifier',
        delete: 'Supprimer',
        restore: 'Restaurer',
        moveToBin: 'Mettre à la corbeille',
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
    status: {
        available: 'Disponible',
        unavailable: 'Indisponible',
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
    create: {
        title: 'Créer un plat',
        breadcrumbs: {
            dishes: 'Plats',
            create: 'Créer',
        },
        sections: {
            dishInformation: 'Informations du plat',
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
        buttons: {
            create: 'Créer',
            cancel: 'Annuler',
        },
        messages: {
            created: 'Le plat a été créé avec succès.',
            error: 'Une erreur est survenue. Veuillez vérifier le formulaire.',
        },
    },
};
