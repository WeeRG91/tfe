export default {
    buttons: {
        login: 'Log in',
        createAccount: 'Create account',
        sendResetLink: 'Send password reset link',
        resetPassword: 'Reset password',
        resendVerificationEmail: 'Resend verification email',
        confirmPassword: 'Confirm Password',
        goBack: 'Go Back',
    },
    links: {
        needHelp: 'Need help?',
        contactSupport: 'Contact support',
        forgotPassword: 'Forgot password?',
        createAccount: 'Create new account',
        backToSignIn: 'Back to Sign In',
        goToHome: 'Go to Home',
    },
    linkExpired: {
        activateTitle: 'Activation Link Expired',
        reactivateTitle: 'Reactivation Link Expired',
        verificationTitle: 'Verification Link Expired',
        heading: {
            badge: 'Link Expired',
            activateDescription:
                'The activation link is no longer valid. This usually happens when:',
            reactivateDescription:
                'The reactivation link is no longer valid. This usually happens when:',
            verificationDescription:
                'The verification link is no longer valid. This usually happens when:',
        },
        reasons: {
            expired: 'The link has expired (links are valid for 24 hours)',
            alreadyActivated: "You've already activated your account",
            alreadyReactivated: "You've already reactivated your account",
            alreadyVerified: "You've already verified your new email address",
            modified: 'The link was modified or corrupted',
        },
        fields: {
            requestNewLink: 'Request new link',
            emailAddress: 'Email address',
            emailPlaceholder: 'Enter your email address',
            resendActivationLink: 'Resend Activation Link',
            resendReactivationLink: 'Resend Reactivation Link',
            resendVerificationLink: 'Resend Verification Link',
        },
        messages: {
            activateSuccess:
                'Activation link sent successfully! Please check your email.',
            reactivateSuccess:
                'Reactivation link sent successfully! Please check your email.',
            verificationSuccess:
                'Verification link sent successfully! Please check your email.',
            error: 'Something went wrong. Please try again.',
        },
    },
};
