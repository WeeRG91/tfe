export default {
    title: 'Allergens',
    table: {
        name: 'Name',
        createdAt: 'Created at',
        updatedAt: 'Updated at',
        deletedAt: 'Deleted at',
    },
    messages: {
        noAllergen: 'No allergens yet',
        confirmRestore: 'Are you sure you want to restore this allergen?',
        confirmDelete:
            'Are you sure you want to permanently delete this allergen?',
        confirmMoveToBin:
            'Are you sure you want to move this allergen to the bin?',
    },
    errors: {
        loadFailed: 'Failed to load more allergens.',
        binFailed: 'Failed to move allergen to bin.',
        restoreFailed: 'Failed to restore allergen.',
        deleteFailed: 'Failed to delete allergen.',
    },
    form: {
        title: 'Create allergen',
        editTitle: 'Edit allergen: {name}',
        breadcrumbs: {
            allergens: 'Allergens',
            create: 'Create',
            edit: 'Edit',
        },
        sections: {
            information: 'Allergen Information',
            photos: 'Photos',
        },
        fields: {
            name: 'Name',
            description: 'Description',
            ingredients: 'Ingredients',
        },
        messages: {
            created: 'Allergen successfully created.',
            edited: 'Allergen successfully edited.',
            error: 'Something went wrong. Please check the form.',
        },
    },
};
