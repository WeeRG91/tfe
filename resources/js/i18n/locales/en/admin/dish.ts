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
    form: {
        title: 'Create dish',
        editTitle: "Edit the dish '{name}'",
        breadcrumbs: {
            dishes: 'Dishes',
            create: 'Create',
            edit: 'Edit',
        },
        sections: {
            information: 'Dish Information',
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
        messages: {
            created: 'Dish successfully created.',
            edited: 'Dish successfully edited.',
            error: 'Something went wrong. Please check the form.',
        },
    },
};
