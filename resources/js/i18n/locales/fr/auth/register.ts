export default {
    title: 'Inscription',
    heading: 'Créer un compte',
    subtitle: 'Saisissez vos informations pour créer votre compte',
    badge: 'Bienvenue',
    fields: {
        name: 'Nom',
        email: 'Adresse e-mail',
        password: 'Mot de passe',
        confirmPassword: 'Confirmer le mot de passe',
    },
    placeholders: {
        name: 'Nom complet',
        email: 'email@exemple.com',
        password: 'Créez un mot de passe sécurisé',
        confirmPassword: 'Confirmez votre mot de passe',
    },
    passwordRequirements: {
        length: '8 caractères ou plus',
        uppercase: 'Une majuscule',
        lowercase: 'Une minuscule',
        number: 'Un chiffre',
        symbol: 'Un symbole',
    },
    messages: {
        created: 'Le compte a été créé avec succès.',
        invalidInput: 'Données invalides. Veuillez vérifier le formulaire.',
        error: 'Une erreur est survenue. Veuillez vérifier le formulaire.',
    },
};
