export default {
    title: 'Dishes',
    categories: {
        all: 'All',
        appetizer: 'Appetizer',
        mainCourse: 'Main course',
        soup: 'Soup',
        noodles: 'Noodles',
        dessert: 'Dessert',
        vegetarian: 'Vegetarian',
    },
    spicyLevel: {
        noSpicy: 'No spicy',
        mild: 'Mild',
        spicy: 'Spicy',
        hot: 'Hot',
    },
    filters: {
        all: 'All',
        available: 'Available',
        unavailable: 'Unavailable',
        deleted: 'Deleted',
        activeFilters: 'Active filters:',
        clearAll: 'Clear all',
        allCategories: 'All categories',
        searchPlaceholder: 'Search dishes...',
    },
    buttons: {
        add: 'Add',
        edit: 'Edit',
        delete: 'Delete',
        restore: 'Restore',
        moveToBin: 'Move to bin',
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
    status: {
        available: 'Available',
        unavailable: 'Unavailable',
    },
    messages: {
        noDish: 'No dishes yet',
        confirmRestore: 'Are you sure you want to restore this dish?',
        confirmDelete: 'Are you sure you want to permanently delete this dish?',
        confirmBin: 'Are you sure you want to move this dish to the bin?',
        confirmAvailable:
            'Are you sure you want to mark this dish as available?',
        confirmUnavailable:
            'Are you sure you want to mark this dish as unavailable?',
    },
    errors: {
        loadFailed: 'Failed to load more dishes.',
        availabilityFailed: 'Failed to update dish availability.',
        binFailed: 'Failed to move dish to bin.',
        restoreFailed: 'Failed to restore dish.',
        deleteFailed: 'Failed to delete dish.',
    },
    create: {
        title: 'Create a dish',
        breadcrumbs: {
            dishes: 'Dishes',
            create: 'Create',
        },
        sections: {
            dishInformation: 'Dish Information',
            photos: 'Photos',
        },
        fields: {
            name: 'Name',
            description: 'Description',
            price: 'Price (€)',
            category: 'Category',
            ingredients: 'Ingredients',
            meatOptions: 'Meat options',
            defaultSpicyLevel:
                'Select the default spiciness level for this dish',
        },
        buttons: {
            create: 'Create',
            cancel: 'Cancel',
        },
        messages: {
            created: 'Dish successfully created.',
            error: 'Something went wrong. Please check the form.',
        },
    },
};
