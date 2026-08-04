export default {
    title: 'Meats',
    table: {
        name: 'Name',
        extraPrice: 'Extra price',
        createdAt: 'Created at',
        updatedAt: 'Updated at',
        deletedAt: 'Deleted at',
    },
    messages: {
        noMeat: 'No meats yet',
        confirmRestore: 'Are you sure you want to restore this meat?',
        confirmDelete: 'Are you sure you want to permanently delete this meat?',
        confirmMoveToBin: 'Are you sure you want to move this meat to the bin?',
    },
    errors: {
        loadFailed: 'Failed to load more meats.',
        binFailed: 'Failed to move meat to bin.',
        restoreFailed: 'Failed to restore meat.',
        deleteFailed: 'Failed to delete meat.',
    },
    form: {
        title: 'Create meat',
        editTitle: 'Edit meat: {name}',
        breadcrumbs: {
            meats: 'Meats',
            create: 'Create',
            edit: 'Edit',
        },
        sections: {
            information: 'Meat Information',
            photos: 'Photos',
        },
        fields: {
            name: 'Name',
            description: 'Description',
            extraPrice: 'Extra price',
        },
        messages: {
            created: 'Meat successfully created.',
            edited: 'Meat successfully edited.',
            error: 'Something went wrong. Please check the form.',
        },
    },
    createModal: {
        title: 'Create Meat Option',
        description:
            'Fill in the information below to create a new meat option.',
        fields: {
            name: 'Name',
            description: 'Description',
            extraPrice: 'Extra price',
        },
        error: 'Something went wrong. Please check the form.',
    },
};
