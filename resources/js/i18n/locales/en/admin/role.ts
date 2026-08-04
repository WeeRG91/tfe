export default {
    title: 'Roles',
    subtitle: 'Manage roles and their permissions',
    permissions: {
        label: '{count} permissions',
        title: 'Permissions',
        noPermissions: 'No permissions assigned',
        uncategorized: 'Uncategorized',
    },
    dates: { updated: 'Updated: {date}' },
    messages: {
        loadMore: 'Load more roles',
        noSearchResults: 'No roles found matching "{query}"',
        confirmDelete: 'Are you sure you want to permanently delete this role?',
    },
    errors: {
        loadFailed: 'Failed to load roles.',
        deleteFailed: 'Failed to delete role.',
    },
    form: {
        createTitle: 'Create Role',
        editTitle: 'Edit Role',
        sections: {
            roleInformation: 'Role Information',
            permissions: 'Permissions',
        },
        fields: { roleName: 'Role Name' },
        placeholders: { roleName: 'Enter role name (e.g., Editor, Manager)' },
        descriptions: { permissions: 'Select permissions for this role' },
        labels: { selected: '{count} selected' },
        messages: {
            created: 'Role successfully created!',
            edited: 'Role successfully updated!',
            invalidInput: 'Invalid input.',
            error: 'Something went wrong. Please check the form.',
        },
    },
};
