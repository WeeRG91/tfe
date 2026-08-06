export default {
    userMenu: {
        settings: 'Paramètres',
        logout: 'Se déconnecter',
    },
    layout: {
        title: 'Paramètres',
        description: 'Gérez votre profil et les paramètres de votre compte',
        navigation: {
            profile: 'Profil',
            password: 'Mot de passe',
            twoFactorAuth: 'Authentification à deux facteurs',
            appearance: 'Apparence',
        },
    },
    profile: {
        title: 'Paramètres du profil',
        heading: {
            title: 'Informations du profil',
            description: 'Mettez à jour votre nom et votre adresse e-mail',
        },
        fields: {
            avatar: 'Photo de profil',
            name: 'Nom',
            email: 'Adresse e-mail',
        },
        placeholders: { name: 'Nom complet', email: 'Adresse e-mail' },
        avatar: {
            uploadHint:
                'Cliquez sur l’icône de l’appareil photo pour télécharger une nouvelle photo',
            requirements: 'JPG, JPEG, PNG, GIF, WEBP. Taille maximale : 2 Mo',
        },
        verification: {
            unverified: 'Votre adresse e-mail n’est pas vérifiée.',
            resend: 'Cliquez ici pour renvoyer l’e-mail de vérification.',
            linkSent:
                'Un nouveau lien de vérification a été envoyé à votre adresse e-mail.',
        },
        messages: { saved: 'Enregistré.' },
        errors: {
            photoTooLarge: 'La photo ne peut pas dépasser 2 Mo.',
            invalidPhoto:
                'Veuillez télécharger une image valide (JPG, JPEG, PNG, GIF ou WEBP).',
        },
    },
    deleteAccount: {
        title: 'Supprimer le compte',
        description: 'Supprimez votre compte ainsi que toutes ses ressources.',
        warning: {
            title: 'Attention',
            description:
                'Veuillez procéder avec prudence. Cette action est irréversible.',
        },
        dialog: {
            title: 'Êtes-vous sûr de vouloir supprimer votre compte ?',
            description:
                'Une fois votre compte supprimé, toutes vos ressources et vos données seront définitivement effacées. Veuillez saisir votre mot de passe pour confirmer la suppression définitive de votre compte.',
        },
        fields: {
            password: 'Mot de passe',
        },
        placeholders: {
            password: 'Mot de passe',
        },
        buttons: {
            delete: 'Supprimer le compte',
        },
    },
    password: {
        title: 'Paramètres du mot de passe',
        heading: {
            title: 'Modifier le mot de passe',
            description:
                'Assurez-vous que votre compte utilise un mot de passe long et complexe afin de rester sécurisé.',
        },
        fields: {
            currentPassword: 'Mot de passe actuel',
            newPassword: 'Nouveau mot de passe',
            confirmPassword: 'Confirmer le mot de passe',
        },
        placeholders: {
            currentPassword: 'Mot de passe actuel',
            newPassword: 'Nouveau mot de passe',
            confirmPassword: 'Confirmer le mot de passe',
        },
        passwordRequirements: {
            characters: '8 caractères ou plus',
            uppercase: 'Majuscule',
            lowercase: 'Minuscule',
            number: 'Chiffre',
            symbol: 'Symbole',
        },
        messages: {
            saved: 'Enregistré.',
        },
    },
    twoFactorAuth: {
        title: 'Authentification à deux facteurs',
        heading: {
            title: 'Authentification à deux facteurs',
            description:
                'Gérez les paramètres de votre authentification à deux facteurs.',
        },
        status: {
            enabled: 'Activée',
            disabled: 'Désactivée',
        },
        descriptions: {
            disabled:
                "Lorsque vous activez l'authentification à deux facteurs, un code PIN sécurisé vous sera demandé lors de la connexion. Ce code est disponible dans une application compatible TOTP sur votre téléphone.",
            enabled:
                "Une fois l'authentification à deux facteurs activée, un code PIN sécurisé vous sera demandé à chaque connexion. Vous pourrez le récupérer dans une application compatible TOTP sur votre téléphone.",
        },
        buttons: {
            continueSetup: 'Continuer la configuration',
            enable: "Activer l'authentification à deux facteurs",
            disable: "Désactiver l'authentification à deux facteurs",
        },
    },
    twoFactorRecovery: {
        title: 'Codes de récupération 2FA',
        description:
            'Les codes de récupération vous permettent de retrouver l’accès à votre compte si vous perdez votre appareil d’authentification à deux facteurs. Conservez-les dans un gestionnaire de mots de passe sécurisé.',
        buttons: {
            view: 'Afficher les codes de récupération',
            hide: 'Masquer les codes de récupération',
            regenerate: 'Régénérer les codes',
        },
        information: {
            usage: 'Chaque code de récupération ne peut être utilisé qu’une seule fois pour accéder à votre compte et sera supprimé après son utilisation. Si vous avez besoin de nouveaux codes, cliquez sur « Régénérer les codes » ci-dessus.',
        },
    },
    twoFactorModal: {
        enabled: {
            title: 'Authentification à deux facteurs activée',
            description:
                'L’authentification à deux facteurs est maintenant activée. Scannez le code QR ou saisissez la clé de configuration dans votre application d’authentification.',
            button: 'Fermer',
        },
        verification: {
            title: 'Vérifier le code d’authentification',
            description:
                'Saisissez le code à 6 chiffres provenant de votre application d’authentification.',
            button: 'Continuer',
        },
        setup: {
            title: 'Activer l’authentification à deux facteurs',
            description:
                'Pour terminer l’activation de l’authentification à deux facteurs, scannez le code QR ou saisissez la clé de configuration dans votre application d’authentification.',
            button: 'Continuer',
        },
        manualSetup: { separator: 'ou saisissez le code manuellement' },
    },
    appearance: {
        title: "Paramètres d'apparence",
        heading: {
            title: "Paramètres d'apparence",
            description: "Modifiez les paramètres d'apparence de votre compte.",
        },
        options: { light: 'Clair', dark: 'Sombre', system: 'Système' },
    },
};
