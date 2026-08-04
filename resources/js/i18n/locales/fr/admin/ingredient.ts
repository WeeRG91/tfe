export default {
    title: 'Ingrédients',
    allAllergens: 'Tous les allergènes',
    table: {
        name: 'Nom',
        allergen: 'Allergène',
        createdAt: 'Créé le',
        updatedAt: 'Mis à jour le',
        deletedAt: 'Supprimé le',
    },
    messages: {
        noDrink: 'Aucune ingrédient pour le moment',
        confirmRestore: 'Voulez-vous vraiment restaurer cet ingrédient ?',
        confirmDelete:
            'Voulez-vous vraiment supprimer définitivement cet ingrédient ?',
        confirmMoveToBin:
            'Voulez-vous vraiment mettre cet ingrédient à la corbeille ?',
    },
    errors: {
        loadFailed: "Impossible de charger davantage d'ingrédient.",
        binFailed: "Impossible de déplacer l'ingrédient dans la corbeille.",
        restoreFailed: "Impossible de restaurer l'ingrédient.",
        deleteFailed: "Impossible de supprimer l'ingrédient.",
    },
    form: {
        title: 'Créer un ingrédient',
        editTitle: "Modifier l'ingrédient : {name}",
        breadcrumbs: {
            ingredients: 'Ingrédients',
            create: 'Créer',
            edit: 'Editer',
        },
        sections: {
            information: "Informations sur l'ingrédient",
            photos: 'Photos',
        },
        fields: {
            name: 'Nom',
            description: 'Description',
            allergen: 'Allergène',
        },
        messages: {
            created: "L'ingrédient a été créé avec succès.",
            updated: "L'ingrédient a été mis à jour avec succès.",
            error: 'Une erreur est survenue. Veuillez vérifier le formulaire.',
        },
    },
    createModal: {
        title: 'En Zutat uleeën',
        description:
            "Fëllt d'Informatiounen hei ënnen aus, fir eng nei Zutat unzeleeën.",
        fields: {
            name: 'Numm',
            description: 'Beschreiwung',
            allergen: 'Allergen',
        },
        error: 'E Feeler ass opgetrueden. Kontrolléiert w.e.g. de Formulaire.',
    },
};
