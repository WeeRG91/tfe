export default {
    pageTitle: 'My Profile',
    eyebrow: 'My Account',
    title: 'My Profile',
    description: 'Manage your account settings and preferences',
    tabs: {
        info: {
            label: 'Profile Info',
            description: 'Update your personal information',
        },
        password: {
            label: 'Password',
            description: 'Change your password',
        },
        twoFactor: {
            label: 'Two-Factor Auth (2FA)',
            description: 'Add extra security to your account',
        },
    },
    infoTab: {
        title: 'Profile Information',
        editProfile: 'Edit Profile',
        verification: {
            unverified: 'Your email address is unverified.',
            resend: 'Click here to resend the verification email.',
            sent: 'A new verification link has been sent to your email address.',
            sentSuccess: 'Verification email sent successfully!',
            sentFailed:
                'Unable to send the verification email. Please try again.',
        },
        photo: {
            label: 'Profile Photo',
            change: 'Change photo',
            requirements: 'JPG, JPEG, PNG, GIF, WEBP • Max. 2 MB',
            tooLarge: 'Photo size must be less than 2 MB.',
            invalidType:
                'Please upload a valid image (JPEG, PNG, GIF, or WEBP).',
            alt: 'Profile photo',
        },
        form: {
            fullName: 'Full Name',
            fullNamePlaceholder: 'Enter your full name',
            email: 'Email Address',
            emailPlaceholder: 'Enter your email address',
            save: 'Save Changes',
            saving: 'Saving...',
            cancel: 'Cancel',
        },
        delete: {
            title: 'Delete Account',
            description:
                'Once your account is deleted, all of its resources and data will be permanently deleted. Please be certain before proceeding.',
            button: 'Delete Account',
        },
        success: {
            updated: 'Profile updated successfully!',
        },
    },
    passwordTab: {
        title: 'Change Password',
        description:
            'Ensure your account is using a strong password to stay secure.',
        form: {
            currentPassword: 'Current Password',
            currentPasswordPlaceholder: 'Enter your current password',
            newPassword: 'New Password',
            newPasswordPlaceholder: 'Enter your new password',
            confirmPassword: 'Confirm New Password',
            confirmPasswordPlaceholder: 'Confirm your new password',
            update: 'Update Password',
            updating: 'Updating...',
        },
        passwordStrength: {
            minLength: '8+ characters',
            uppercase: 'Uppercase',
            lowercase: 'Lowercase',
            number: 'Number',
            symbol: 'Symbol',
        },
        success: {
            updated: 'Password updated successfully!',
        },
    },
    twoFactorTab: {
        title: 'Two-Factor Authentication',
        description: 'Add an extra layer of security to your account',
        status: {
            enabled: 'Enabled',
            disabled: 'Disabled',
            protected: 'Your account is protected with 2FA.',
            unprotected: 'Your account is not protected with 2FA.',
        },
        actions: {
            enable: 'Enable 2FA',
            disable: 'Disable 2FA',
            loading: 'Loading...',
            verify: 'Verify & Enable',
            verifying: 'Verifying...',
            showRecoveryCodes: 'Show recovery codes',
            hideRecoveryCodes: 'Hide recovery codes',
            copy: 'Copy',
            copied: 'Copied!',
            download: 'Download',
            regenerate: 'Regenerate',
        },
        setup: {
            step1Title: 'Step 1: Scan QR Code',
            step1Description:
                'Scan the QR code with your authenticator app (Google Authenticator, Authy, etc.).',
            manualEntry:
                "If you can't scan the QR code, enter this secret key manually into your authenticator app.",
            appName: 'App name',
            account: 'Account',
            step2Title: 'Step 2: Verify Code',
            step2Description:
                'Enter the 6-digit code from your authenticator app.',
            verificationCode: 'Verification Code',
            verificationPlaceholder: 'Enter 6-digit code',
            incompleteCode: 'Please enter all 6 digits.',
        },
        recoveryCodes: {
            title: 'Save Your Recovery Codes',
            description:
                'These codes can be used to access your account if you lose your authenticator device. Store them in a safe place.',
        },
        enabledNotice: {
            title: 'Two-factor authentication is enabled.',
            description:
                "Your account is protected with an extra layer of security. You'll need your authenticator app to sign in.",
        },
        success: {
            setupStarted: 'Scan the QR code with your authenticator app.',
            enabled: 'Two-factor authentication enabled successfully!',
            disabled: 'Two-factor authentication disabled.',
            regenerated: 'Recovery codes regenerated successfully!',
            copiedSecretKey: 'Secret key copied.',
        },
        errors: {
            setupFailed: 'Unable to set up two-factor authentication.',
            invalidCode: 'Invalid authentication code. Please try again.',
            disableFailed: 'Unable to disable two-factor authentication.',
            regenerateFailed: 'Unable to regenerate recovery codes.',
            loadRecoveryCodes: 'Unable to load recovery codes.',
            copyFailed: 'Failed to copy recovery codes.',
        },
    },
    confirmPasswordModal: {
        password: 'Password',
        passwordPlaceholder: 'Enter your password',
        cancel: 'Cancel',
        confirm: 'Confirm',
        confirming: 'Verifying...',
        close: 'Close',
        enableTwoFactor: {
            title: 'Enable Two-Factor Authentication',
            description:
                'Please enter your password to enable two-factor authentication.',
            confirm: 'Enable 2FA',
        },
        disableTwoFactor: {
            title: 'Disable Two-Factor Authentication',
            description:
                'Please enter your password to disable two-factor authentication.',
            confirm: 'Disable 2FA',
        },
        regenerateCodes: {
            title: 'Regenerate Recovery Codes',
            description:
                'Please enter your password to regenerate your recovery codes.',
            confirm: 'Regenerate Codes',
        },
        default: {
            title: 'Confirm Password',
            description: 'Please enter your password.',
        },

        errors: {
            required: 'Password is required.',
            invalid: 'Invalid password. Please try again.',
            verificationFailed:
                'Unable to verify your password. Please try again.',
        },
    },
};
