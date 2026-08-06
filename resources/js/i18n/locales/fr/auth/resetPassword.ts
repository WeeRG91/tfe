export default {
    title: 'Réinitialiser le mot de passe',
    heading: 'Réinitialisez votre mot de passe',
    subtitle: 'Veuillez saisir votre nouveau mot de passe ci-dessous',
    badge: 'Mot de passe',
    fields: {
        email: 'Adresse e-mail',
        password: 'Mot de passe',
        confirmPassword: 'Confirmer le mot de passe',
    },
    placeholders: {
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
        resetSuccess: 'Le mot de passe a été réinitialisé avec succès.',
        invalidInput: 'Données invalides. Veuillez vérifier le formulaire.',
        error: 'Une erreur est survenue. Veuillez vérifier le formulaire.',
    },
};
