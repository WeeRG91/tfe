export default {
    userMenu: {
        settings: 'Settings',
        logout: 'Log out',
    },
    layout: {
        title: 'Settings',
        description: 'Manage your profile and account settings',
        navigation: {
            profile: 'Profile',
            password: 'Password',
            twoFactorAuth: 'Two-Factor Authentication',
            appearance: 'Appearance',
        },
    },
    profile: {
        title: 'Profile Settings',
        heading: {
            title: 'Profile Information',
            description: 'Update your name and email address',
        },
        fields: { avatar: 'Avatar', name: 'Name', email: 'Email Address' },
        placeholders: { name: 'Full name', email: 'Email address' },
        avatar: {
            uploadHint: 'Click the camera icon to upload a new photo',
            requirements: 'JPG, JPEG, PNG, GIF, WEBP. Max: 2 MB',
        },
        verification: {
            unverified: 'Your email address is unverified.',
            resend: 'Click here to resend the verification email.',
            linkSent:
                'A new verification link has been sent to your email address.',
        },
        messages: { saved: 'Saved.' },
        errors: {
            photoTooLarge: 'Photo size must be less than 2 MB.',
            invalidPhoto:
                'Please upload a valid image (JPG, JPEG, PNG, GIF, or WEBP).',
        },
    },
    deleteAccount: {
        title: 'Delete Account',
        description: 'Delete your account and all of its resources',
        warning: {
            title: 'Warning',
            description:
                'Please proceed with caution. This action cannot be undone.',
        },
        dialog: {
            title: 'Are you sure you want to delete your account?',
            description:
                'Once your account is deleted, all of your resources and data will be permanently removed. Please enter your password to confirm that you want to permanently delete your account.',
        },
        fields: {
            password: 'Password',
        },
        placeholders: {
            password: 'Password',
        },
        buttons: {
            delete: 'Delete Account',
        },
    },
    password: {
        title: 'Password Settings',
        heading: {
            title: 'Update Password',
            description:
                'Ensure your account is using a long, random password to stay secure.',
        },
        fields: {
            currentPassword: 'Current Password',
            newPassword: 'New Password',
            confirmPassword: 'Confirm Password',
        },
        placeholders: {
            currentPassword: 'Current password',
            newPassword: 'New password',
            confirmPassword: 'Confirm password',
        },
        passwordRequirements: {
            characters: '8+ characters',
            uppercase: 'Uppercase',
            lowercase: 'Lowercase',
            number: 'Number',
            symbol: 'Symbol',
        },
        messages: {
            saved: 'Saved.',
        },
    },
    twoFactorAuth: {
        title: 'Two-Factor Authentication',
        heading: {
            title: 'Two-Factor Authentication',
            description: 'Manage your two-factor authentication settings',
        },
        status: {
            enabled: 'Enabled',
            disabled: 'Disabled',
        },
        descriptions: {
            disabled:
                'When you enable two-factor authentication, you will be prompted for a secure PIN during login. This PIN can be retrieved from a TOTP-compatible application on your phone.',
            enabled:
                'With two-factor authentication enabled, you will be prompted for a secure PIN during login. You can retrieve this PIN from a TOTP-compatible application on your phone.',
        },
        buttons: {
            continueSetup: 'Continue Setup',
            enable: 'Enable 2FA',
            disable: 'Disable 2FA',
        },
    },
    twoFactorRecovery: {
        title: '2FA Recovery Codes',
        description:
            'Recovery codes let you regain access if you lose your 2FA device. Store them in a secure password manager.',
        buttons: {
            view: 'View Recovery Codes',
            hide: 'Hide Recovery Codes',
            regenerate: 'Regenerate Codes',
        },
        information: {
            usage: 'Each recovery code can be used once to access your account and will be removed after use. If you need more, click “Regenerate Codes” above.',
        },
    },
    twoFactorModal: {
        enabled: {
            title: 'Two-Factor Authentication Enabled',
            description:
                'Two-factor authentication is now enabled. Scan the QR code or enter the setup key in your authenticator app.',
            button: 'Close',
        },
        verification: {
            title: 'Verify Authentication Code',
            description: 'Enter the 6-digit code from your authenticator app.',
            button: 'Continue',
        },
        setup: {
            title: 'Enable Two-Factor Authentication',
            description:
                'To finish enabling two-factor authentication, scan the QR code or enter the setup key in your authenticator app.',
            button: 'Continue',
        },
        manualSetup: { separator: 'or, enter the code manually' },
    },
    appearance: {
        title: 'Appearance Settings',
        heading: {
            title: 'Appearance Settings',
            description: "Update your account's appearance settings.",
        },
        options: {
            light: 'Light',
            dark: 'Dark',
            system: 'System',
            systemDescription:
                'Automatically follow the light or dark appearance of this device.',
        },
        restaurantDefault: {
            title: 'Restaurant default theme',
            description:
                'Choose the theme clients and guests see until they select their own appearance.',
            save: 'Save default theme',
            saved: 'The default client theme was updated.',
        },
        customThemes: {
            title: 'Custom themes',
            description:
                'Start from a built-in theme. Your new theme stays private until published.',
            name: 'Theme name',
            baseTheme: 'Start from',
            create: 'Create draft',
            created: 'Draft theme created.',
            published: 'Published',
            draft: 'Draft',
            empty: 'No custom themes yet.',
            saved: 'Theme saved.',
            surfaceColors: 'Advanced: surfaces and text',
            feedbackColors: 'Advanced: status colors',
            sidebarColors: 'Advanced: sidebar',
            chartColors: 'Advanced: charts',
            publish: 'Publish theme',
            saveBeforePublish: 'Save your changes before publishing.',
            publishFailed:
                'This theme cannot be published yet. Fix the highlighted colors.',
            publishedSuccess: 'Theme published. Clients can now select it.',
            delete: 'Delete theme',
            deleteConfirmation:
                'Delete "{name}"? It will disappear from theme choices. If it is the restaurant default, Light will replace it. People using it personally will return to their default.',
            deleted: 'Theme deleted.',
            colors: {
                primary: 'Primary',
                primaryForeground: 'Text on primary',
                secondary: 'Secondary',
                secondaryForeground: 'Text on secondary',
                accent: 'Accent',
                accentForeground: 'Text on accent',
                background: 'Page background',
                foreground: 'Page text',
                card: 'Card background',
                cardForeground: 'Card text',
                popover: 'Popover background',
                popoverForeground: 'Popover text',
                muted: 'Muted background',
                mutedForeground: 'Muted text',
                border: 'Border',
                input: 'Input border',
                ring: 'Focus ring',
                destructive: 'Error',
                destructiveForeground: 'Text on error',
                success: 'Success',
                successForeground: 'Text on success',
                warning: 'Warning',
                warningForeground: 'Text on warning',
                info: 'Information',
                infoForeground: 'Text on information',
                sidebarBackground: 'Sidebar background',
                sidebarForeground: 'Sidebar text',
                sidebarPrimary: 'Sidebar primary',
                sidebarPrimaryForeground: 'Text on sidebar primary',
                sidebarAccent: 'Sidebar accent',
                sidebarAccentForeground: 'Text on sidebar accent',
                sidebarBorder: 'Sidebar border',
                sidebarRing: 'Sidebar focus ring',
                chart1: 'Chart 1',
                chart2: 'Chart 2',
                chart3: 'Chart 3',
                chart4: 'Chart 4',
                chart5: 'Chart 5',
            },
        },
    },
};
