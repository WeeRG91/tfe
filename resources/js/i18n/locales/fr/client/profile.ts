export default {
    pageTitle: 'Mon profil',
    eyebrow: 'Mon compte',
    title: 'Mon profil',
    description: 'Gérez les paramètres et les préférences de votre compte',
    tabs: {
        info: {
            label: 'Informations',
            description: 'Modifiez vos informations personnelles',
        },
        password: {
            label: 'Mot de passe',
            description: 'Modifiez votre mot de passe',
        },
        twoFactor: {
            label: 'Double authentification (2FA)',
            description: 'Renforcez la sécurité de votre compte',
        },
    },
    infoTab: {
        title: 'Informations du profil',
        editProfile: 'Modifier le profil',
        verification: {
            unverified: "Votre adresse e-mail n'est pas vérifiée.",
            resend: 'Cliquez ici pour renvoyer l’e-mail de vérification.',
            sent: 'Un nouveau lien de vérification a été envoyé à votre adresse e-mail.',
            sentSuccess: 'E-mail de vérification envoyé avec succès.',
            sentFailed:
                "Impossible d'envoyer l’e-mail de vérification. Veuillez réessayer.",
        },
        photo: {
            label: 'Photo de profil',
            change: 'Changer la photo',
            requirements: 'JPG, JPEG, PNG, GIF, WEBP • Max. 2 Mo',
            tooLarge: 'La photo ne peut pas dépasser 2 Mo.',
            invalidType:
                'Veuillez sélectionner une image valide (JPEG, PNG, GIF ou WEBP).',
            alt: 'Photo de profil',
        },
        form: {
            fullName: 'Nom complet',
            fullNamePlaceholder: 'Saisissez votre nom complet',
            email: 'Adresse e-mail',
            emailPlaceholder: 'Saisissez votre adresse e-mail',
            save: 'Enregistrer',
            saving: 'Enregistrement...',
            cancel: 'Annuler',
        },
        delete: {
            title: 'Supprimer le compte',
            description:
                'Une fois votre compte supprimé, toutes vos données et ressources seront définitivement effacées. Veuillez confirmer votre choix avant de continuer.',
            button: 'Supprimer le compte',
        },
        success: {
            updated: 'Profil mis à jour avec succès !',
        },
    },
    passwordTab: {
        title: 'Modifier le mot de passe',
        description:
            'Utilisez un mot de passe fort pour renforcer la sécurité de votre compte.',
        form: {
            currentPassword: 'Mot de passe actuel',
            currentPasswordPlaceholder: 'Saisissez votre mot de passe actuel',
            newPassword: 'Nouveau mot de passe',
            newPasswordPlaceholder: 'Saisissez votre nouveau mot de passe',
            confirmPassword: 'Confirmer le nouveau mot de passe',
            confirmPasswordPlaceholder: 'Confirmez votre nouveau mot de passe',
            update: 'Mettre à jour le mot de passe',
            updating: 'Mise à jour...',
        },
        passwordStrength: {
            minLength: '8+ caractères',
            uppercase: 'Majuscule',
            lowercase: 'Minuscule',
            number: 'Chiffre',
            symbol: 'Symbole',
        },
        success: {
            updated: 'Mot de passe mis à jour avec succès !',
        },
    },
    twoFactorTab: {
        title: 'Authentification à deux facteurs',
        description:
            'Ajoutez une couche de sécurité supplémentaire à votre compte',
        status: {
            enabled: 'Activée',
            disabled: 'Désactivée',
            protected:
                'Votre compte est protégé par la double authentification.',
            unprotected:
                "Votre compte n'est pas protégé par la double authentification.",
        },
        actions: {
            enable: 'Activer la double authentification',
            disable: 'Désactiver la double authentification',
            loading: 'Chargement...',
            verify: 'Vérifier et activer',
            verifying: 'Vérification...',
            showRecoveryCodes: 'Afficher les codes de récupération',
            hideRecoveryCodes: 'Masquer les codes de récupération',
            copy: 'Copier',
            copied: 'Copié !',
            download: 'Télécharger',
            regenerate: 'Régénérer',
        },
        setup: {
            step1Title: 'Étape 1 : Scanner le QR code',
            step1Description:
                "Scannez le QR code avec votre application d'authentification (Google Authenticator, Authy, etc.).",
            manualEntry:
                'Si vous ne pouvez pas scanner le QR code, saisissez cette clé secrète manuellement dans votre application.',
            appName: 'Application',
            account: 'Compte',
            step2Title: 'Étape 2 : Vérifier le code',
            step2Description:
                "Saisissez le code à 6 chiffres de votre application d'authentification.",
            verificationCode: 'Code de vérification',
            verificationPlaceholder: 'Saisissez le code à 6 chiffres',
            incompleteCode: 'Veuillez saisir les 6 chiffres.',
        },
        recoveryCodes: {
            title: 'Enregistrez vos codes de récupération',
            description:
                "Ces codes permettent d'accéder à votre compte si vous perdez votre appareil d'authentification. Conservez-les dans un endroit sûr.",
        },
        enabledNotice: {
            title: 'La double authentification est activée.',
            description:
                "Votre compte bénéficie d'une protection supplémentaire. Vous devrez utiliser votre application d'authentification pour vous connecter.",
        },
        success: {
            setupStarted:
                "Scannez le QR code avec votre application d'authentification.",
            enabled: 'Double authentification activée avec succès !',
            disabled: 'Double authentification désactivée.',
            regenerated: 'Codes de récupération régénérés avec succès !',
            copiedSecretKey: 'Clé secrète copiée.',
        },
        errors: {
            setupFailed: 'Impossible de configurer la double authentification.',
            invalidCode:
                "Code d'authentification invalide. Veuillez réessayer.",
            disableFailed:
                'Impossible de désactiver la double authentification.',
            regenerateFailed:
                'Impossible de régénérer les codes de récupération.',
            loadRecoveryCodes:
                'Impossible de charger les codes de récupération.',
            copyFailed: 'Impossible de copier les codes de récupération.',
        },
    },
    confirmPasswordModal: {
        password: 'Mot de passe',
        passwordPlaceholder: 'Saisissez votre mot de passe',
        cancel: 'Annuler',
        confirm: 'Confirmer',
        confirming: 'Vérification...',
        close: 'Fermer',
        enableTwoFactor: {
            title: 'Activer la double authentification',
            description:
                'Saisissez votre mot de passe pour activer la double authentification.',
            confirm: 'Activer la 2FA',
        },
        disableTwoFactor: {
            title: 'Désactiver la double authentification',
            description:
                'Saisissez votre mot de passe pour désactiver la double authentification.',
            confirm: 'Désactiver la 2FA',
        },
        regenerateCodes: {
            title: 'Régénérer les codes de récupération',
            description:
                'Saisissez votre mot de passe pour régénérer vos codes de récupération.',
            confirm: 'Régénérer les codes',
        },
        default: {
            title: 'Confirmer le mot de passe',
            description: 'Saisissez votre mot de passe.',
        },
        errors: {
            required: 'Le mot de passe est obligatoire.',
            invalid: 'Mot de passe incorrect. Veuillez réessayer.',
            verificationFailed:
                'Impossible de vérifier votre mot de passe. Veuillez réessayer.',
        },
    },
};
