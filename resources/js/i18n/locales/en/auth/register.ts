export default {
    title: 'Register',
    heading: 'Create an account',
    subtitle: 'Enter your details below to create your account',
    badge: 'Welcome',
    fields: {
        name: 'Name',
        email: 'Email address',
        password: 'Password',
        confirmPassword: 'Confirm Password',
    },
    placeholders: {
        name: 'Full name',
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
        created: 'Account created successfully!',
        invalidInput: 'Invalid input. Please check the form.',
        error: 'Something went wrong. Please check the form.',
    },
};
