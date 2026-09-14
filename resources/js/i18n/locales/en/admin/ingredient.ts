export default {
    title: 'Ingredients',
    allAllergens: 'All allergens',
    table: {
        name: 'Name',
        allergen: 'Allergen',
        createdAt: 'Created at',
        updatedAt: 'Updated at',
        deletedAt: 'Deleted at',
    },
    messages: {
        noIngredient: 'No ingredients yet',
        confirmRestore: 'Are you sure you want to restore this ingredient?',
        confirmDelete:
            'Are you sure you want to permanently delete this ingredient?',
        confirmMoveToBin:
            'Are you sure you want to move this ingredient to the bin?',
    },
    errors: {
        loadFailed: 'Failed to load more ingredients.',
        binFailed: 'Failed to move ingredient to bin.',
        restoreFailed: 'Failed to restore ingredient.',
        deleteFailed: 'Failed to delete ingredient.',
    },
    form: {
        title: 'Create Ingredient',
        editTitle: 'Edit ingredient: {name}',
        breadcrumbs: {
            ingredients: 'Ingredients',
            create: 'Create',
            edit: 'Edit',
        },
        sections: {
            information: 'Ingredient Information',
            photos: 'Photos',
        },
        fields: {
            name: 'Name',
            description: 'Description',
            allergen: 'Allergen',
        },
        messages: {
            created: 'Ingredient successfully created.',
            edited: 'Ingredient successfully updated.',
            error: 'Something went wrong. Please check the form.',
        },
    },
    createModal: {
        title: 'Create Ingredient',
        description:
            'Fill in the information below to create a new ingredient.',
        fields: {
            name: 'Name',
            description: 'Description',
            allergen: 'Allergen',
        },
        error: 'Something went wrong. Please check the form.',
    },
};
