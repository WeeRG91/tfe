export default {
    title: 'Drinks',
    categories: {
        all: 'All',
        softDrink: 'Soft drink',
        hotDrink: 'Hot drink',
        smoothie: 'Smoothie',
        beer: 'Beer',
        wine: 'Wine',
        cocktail: 'Cocktail',
        mocktail: 'Mocktail',
    },
    table: {
        name: 'Name',
        category: 'Category',
        price: 'Price',
        availability: 'Availability',
        createdAt: 'Created at',
        updatedAt: 'Updated at',
        deletedAt: 'Deleted at',
    },
    messages: {
        noDrink: 'No drinks yet',
        confirmRestore: 'Are you sure you want to restore this drink?',
        confirmDelete:
            'Are you sure you want to permanently delete this drink?',
        confirmMoveToBin:
            'Are you sure you want to move this drink to the bin?',
        confirmAvailable:
            'Are you sure you want to mark this drink as available?',
        confirmUnavailable:
            'Are you sure you want to mark this drink as unavailable?',
    },
    errors: {
        loadFailed: 'Failed to load more drinks.',
        availabilityFailed: 'Failed to update drink availability.',
        binFailed: 'Failed to move drink to bin.',
        restoreFailed: 'Failed to restore drink.',
        deleteFailed: 'Failed to delete drink.',
    },
    form: {
        title: 'Create a drink',
        editTitle: 'Edit drink: {name}',
        breadcrumbs: {
            drinks: 'Drinks',
            create: 'Create',
            edit: 'Edit',
        },
        sections: {
            information: 'Drink Information',
            photos: 'Photos',
        },
        fields: {
            name: 'Name',
            description: 'Description',
            price: 'Price (€)',
            category: 'Category',
        },
        messages: {
            created: 'Drink successfully created.',
            edited: 'Drink successfully edited.',
            error: 'Something went wrong. Please check the form.',
        },
    },
};
