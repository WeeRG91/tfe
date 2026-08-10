export default {
    title: 'Activer le compte',
    activate: 'Activer le compte',
    heading: {
        title: 'Définissez votre mot de passe',
        subtitle:
            'Créez un mot de passe sécurisé pour terminer la configuration de votre compte',
        badge: 'Bienvenue',
    },
    fields: {
        name: 'Nom',
        email: 'E-mail',
        password: 'Mot de passe',
        passwordPlaceholder: 'Créez un mot de passe sécurisé',
        confirmPassword: 'Confirmer le mot de passe',
        confirmPasswordPlaceholder: 'Confirmez votre mot de passe',
    },
    messages: {
        success: 'Compte activé avec succès !',
        invalidInput: 'Données invalides.',
        error: 'Une erreur s’est produite. Veuillez vérifier le formulaire.',
    },
    passwordRequirements: {
        characters: '8 caractères minimum',
        uppercase: 'Majuscule',
        lowercase: 'Minuscule',
        number: 'Chiffre',
        symbol: 'Symbole',
    },
};
