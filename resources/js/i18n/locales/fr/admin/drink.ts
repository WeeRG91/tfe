export default {
    title: 'Boissons',
    categories: {
        all: 'Toutes',
        softDrink: 'Boisson sans alcool',
        hotDrink: 'Boisson chaude',
        smoothie: 'Smoothie',
        beer: 'Bière',
        wine: 'Vin',
        cocktail: 'Cocktail',
        mocktail: 'Cocktail sans alcool',
    },
    table: {
        name: 'Nom',
        category: 'Catégorie',
        price: 'Prix',
        availability: 'Disponibilité',
        createdAt: 'Créée le',
        updatedAt: 'Mise à jour le',
        deletedAt: 'Supprimée le',
    },
    messages: {
        noDrink: 'Aucune boisson pour le moment',
        confirmRestore: 'Voulez-vous vraiment restaurer cette boisson ?',
        confirmDelete:
            'Voulez-vous vraiment supprimer définitivement cette boisson ?',
        confirmMoveToBin:
            'Voulez-vous vraiment mettre cette boisson à la corbeille ?',
        confirmAvailable:
            'Voulez-vous vraiment rendre cette boisson disponible ?',
        confirmUnavailable:
            'Voulez-vous vraiment rendre cette boisson indisponible ?',
    },
    errors: {
        loadFailed: 'Impossible de charger davantage de boissons.',
        availabilityFailed:
            'Impossible de modifier la disponibilité de la boisson.',
        binFailed: 'Impossible de déplacer la boisson dans la corbeille.',
        restoreFailed: 'Impossible de restaurer la boisson.',
        deleteFailed: 'Impossible de supprimer la boisson.',
    },
    form: {
        title: 'Créer une boisson',
        editTitle: 'Modifier la boisson : {name}',
        breadcrumbs: {
            drinks: 'Boissons',
            create: 'Créer',
            edit: 'Modifier',
        },
        sections: {
            information: 'Informations sur la boisson',
            photos: 'Photos',
        },
        fields: {
            name: 'Nom',
            description: 'Description',
            price: 'Prix (€)',
            category: 'Catégorie',
        },
        messages: {
            created: 'La boisson a été créée avec succès.',
            edited: 'La boisson a été mis à jour avec succès.',
            error: 'Une erreur est survenue. Veuillez vérifier le formulaire.',
        },
    },
};
