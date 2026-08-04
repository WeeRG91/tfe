export default {
    title: 'Users',
    subtitle: 'Manage users and their roles',
    messages: {
        noUsers: 'No users found',
        noSearchResults: 'No users found matching "{query}"',
        joined: 'Joined: {date}',
        loadMore: 'Load more users',
        confirmInactivate: 'Are you sure you want to inactivate this user?',
        confirmReactivate: 'Are you sure you want to reactivate this user?',
        confirmDelete: 'Are you sure you want to permanently delete this user?',
    },
    errors: {
        loadFailed: 'Failed to load users.',
        inactivateFailed: 'Failed to inactivate user.',
        reactivateFailed: 'Failed to reactivate user.',
        deleteFailed: 'Failed to delete user.',
    },
    details: {
        joined: 'Joined: {date}',
        stats: {
            roles: 'Roles',
            totalPoints: 'Total Points',
            orders: 'Orders',
            permissions: 'Permissions',
        },
        permissions: {
            title: 'Roles & Permissions',
            assignedRoles: 'Assigned Roles',
            allPermissions: 'All Permissions',
            uncategorized: 'Uncategorized',
            extra: 'Extra',
            both: 'Both',
            noPermissions: 'No permissions assigned',
            noRoles: 'No roles assigned to this user',
        },
        orders: {
            title: 'Order History',
            noOrders: 'No orders found for this user',
        },
        loyaltyPoints: {
            title: 'Loyalty Points History',
            noHistory: 'No loyalty points history',
        },
        dates: { createdAt: 'Created at:', lastUpdated: 'Last updated:' },
        messages: {
            confirmInactivate: 'Are you sure you want to inactivate this user?',
            confirmReactivate: 'Are you sure you want to reactivate this user?',
            confirmDelete:
                'Are you sure you want to permanently delete this user?',
        },
        errors: {
            inactivateFailed: 'Failed to inactivate user.',
            reactivateFailed: 'Failed to reactivate user.',
            deleteFailed: 'Failed to delete user.',
        },
    },
    form: {
        createTitle: 'Create User',
        editTitle: 'Edit User',
        sections: {
            userInformation: 'User Information',
            roleAssignment: 'Role Assignment',
            permissions: 'Permissions',
        },
        fields: {
            fullName: 'Full Name',
            emailAddress: 'Email Address',
            password: 'Password',
            confirmPassword: 'Confirm Password',
            role: 'Role',
        },
        placeholders: {
            fullName: 'Enter full name',
            emailAddress: 'Enter email address',
            password: 'Enter password',
            confirmPassword: 'Confirm password',
            selectRole: 'Select a role',
        },
        descriptions: {
            roleAssignment:
                'Select a role for this user. Permissions will be automatically assigned based on the role.',
            permissions: 'Fine-tune permissions for this user.',
        },
        labels: { selected: '{count} selected' },
        messages: {
            created: 'User successfully created!',
            updated: 'User successfully updated!',
            invalidInput: 'Invalid input.',
            error: 'Something went wrong. Please check the form.',
        },
    },
};
