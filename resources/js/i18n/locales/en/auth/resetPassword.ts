export default {
    title: 'Reset password',
    heading: 'Reset your password',
    subtitle: 'Please enter your new password below',
    badge: 'Password',
    fields: {
        email: 'Email address',
        password: 'Password',
        confirmPassword: 'Confirm Password',
    },
    placeholders: {
        password: 'Create a strong password',
        confirmPassword: 'Confirm your password',
    },
    passwordRequirements: {
        length: '8+ characters',
        uppercase: 'Uppercase',
        lowercase: 'Lowercase',
        number: 'Number',
        symbol: 'Symbol',
    },
    messages: {
        resetSuccess: 'Password reset successfully!',
        invalidInput: 'Invalid input. Please check the form.',
        error: 'Something went wrong. Please check the form.',
    },
};
