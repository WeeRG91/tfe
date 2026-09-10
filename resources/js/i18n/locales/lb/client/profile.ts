export default {
    pageTitle: 'Mäi Profil',
    eyebrow: 'Mäi Kont',
    title: 'Mäi Profil',
    description: 'Verwalt Är Kontastellungen a Preferenzen',
    tabs: {
        info: {
            label: 'Profilinformatiounen',
            description: 'Ännert Är perséinlech Informatiounen',
        },
        password: {
            label: 'Passwuert',
            description: 'Ännert Äert Passwuert',
        },
        twoFactor: {
            label: 'Zwee-Faktor-Auth (2FA)',
            description: 'Maacht Äre Kont méi sécher',
        },
    },
    infoTab: {
        title: 'Profilinformatiounen',
        editProfile: 'Profil änneren',
        verification: {
            unverified: 'Är E-Mail-Adress ass nach net verifizéiert.',
            resend: 'Klickt hei fir d’Verifizéierungs-E-Mail nach eng Kéier ze schécken.',
            sent: 'En neie Verifizéierungslink gouf op Är E-Mail-Adress geschéckt.',
            sentSuccess: 'Verifizéierungs-E-Mail erfollegräich geschéckt.',
            sentFailed:
                'D’Verifizéierungs-E-Mail konnt net geschéckt ginn. Probéiert et w.e.g. nach eng Kéier.',
        },
        photo: {
            label: 'Profilfoto',
            change: 'Foto änneren',
            requirements: 'JPG, JPEG, PNG, GIF, WEBP • Max. 2 MB',
            tooLarge: 'D’Foto däerf net méi grouss wéi 2 MB sinn.',
            invalidType:
                'Lued w.e.g. eng valabel Bilddatei erop (JPEG, PNG, GIF oder WEBP).',
            alt: 'Profilfoto',
            remove: 'Foto läschen',
            removeMessage: 'Wëllt Dir Är Profilfoto wierklech läschen?',
            removed: 'D’Profilfoto gouf geläscht.',
            removeFailed: 'D’Profilfoto konnt net geläscht ginn.',
        },
        form: {
            fullName: 'Vollstännegen Numm',
            fullNamePlaceholder: 'Gitt Äre kompletten Numm an',
            email: 'E-Mail-Adress',
            emailPlaceholder: 'Gitt Är E-Mail-Adress an',
            save: 'Späicheren',
            saving: 'Späichert...',
            cancel: 'Ofbriechen',
        },
        delete: {
            title: 'Kont läschen',
            description:
                'Wann Äre Kont geläscht gëtt, ginn all Är Donnéeën a Ressourcen permanent geläscht. Sidd w.e.g. sécher ier Dir weiderfuert.',
            button: 'Kont läschen',
        },
        success: {
            updated: 'Profil erfollegräich aktualiséiert!',
        },
    },
    passwordTab: {
        title: 'Passwuert änneren',
        description:
            'Benotzt e staarkt Passwuert fir Äre Kont besser ze schützen.',
        form: {
            currentPassword: 'Aktuellt Passwuert',
            currentPasswordPlaceholder: 'Gitt Äert aktuellt Passwuert an',
            newPassword: 'Neit Passwuert',
            newPasswordPlaceholder: 'Gitt Äert neit Passwuert an',
            confirmPassword: 'Neit Passwuert confirméieren',
            confirmPasswordPlaceholder: 'Confirméiert Äert neit Passwuert',
            update: 'Passwuert aktualiséieren',
            updating: 'Aktualiséiert...',
        },
        passwordStrength: {
            minLength: '8+ Zeechen',
            uppercase: 'Groussbuschtaf',
            lowercase: 'Klengbuschtaf',
            number: 'Zuel',
            symbol: 'Symbol',
        },
        success: {
            updated: 'Passwuert erfollegräich aktualiséiert!',
        },
    },
    twoFactorTab: {
        title: 'Zwee-Faktor-Authentifikatioun',
        description:
            'Füügt eng zousätzlech Sécherheetsschicht fir Äre Kont dobäi',
        status: {
            enabled: 'Aktivéiert',
            disabled: 'Desaktivéiert',
            protected: 'Äre Kont ass mat 2FA geschützt.',
            unprotected: 'Äre Kont ass net mat 2FA geschützt.',
        },
        actions: {
            enable: '2FA aktivéieren',
            disable: '2FA desaktivéieren',
            loading: 'Lueden...',
            verify: 'Verifizéieren & aktivéieren',
            verifying: 'Verifizéiert...',
            showRecoveryCodes: 'Recovery-Coden weisen',
            hideRecoveryCodes: 'Recovery-Coden verstoppen',
            copy: 'Kopéieren',
            copied: 'Kopéiert!',
            download: 'Eroflueden',
            regenerate: 'Nei generéieren',
        },
        setup: {
            step1Title: 'Schrëtt 1: QR-Code scannen',
            step1Description:
                'Scannt de QR-Code mat Ärer Authentifikatiouns-App (Google Authenticator, Authy, asw.).',
            manualEntry:
                'Wann Dir de QR-Code net scanne kënnt, gitt dëse geheime Schlëssel manuell an Är App an.',
            appName: 'App',
            account: 'Kont',
            step2Title: 'Schrëtt 2: Code verifizéieren',
            step2Description:
                'Gitt de 6-stellege Code aus Ärer Authentifikatiouns-App an.',
            verificationCode: 'Verifikatiounscode',
            verificationPlaceholder: '6-stellege Code aginn',
            incompleteCode: 'Gitt w.e.g. all 6 Zifferen an.',
        },
        recoveryCodes: {
            title: 'Späichert Är Recovery-Coden',
            description:
                'Mat dëse Coden kënnt Dir op Äre Kont zougräifen, wann Dir Ären Authentifikatiounsapparat verléiert. Späichert se op enger sécherer Plaz.',
        },
        enabledNotice: {
            title: "D'Zwee-Faktor-Authentifikatioun ass aktivéiert.",
            description:
                'Äre Kont ass duerch eng zousätzlech Sécherheet geschützt. Dir braucht Är Authentifikatiouns-App fir Iech unzemellen.',
        },
        success: {
            setupStarted: 'Scannt de QR-Code mat Ärer Authentifikatiouns-App.',
            enabled: 'Zwee-Faktor-Authentifikatioun erfollegräich aktivéiert!',
            disabled: 'Zwee-Faktor-Authentifikatioun desaktivéiert.',
            regenerated: 'Recovery-Coden erfollegräich nei generéiert!',
            copiedSecretKey: 'Geheime Schlëssel kopéiert.',
        },
        errors: {
            setupFailed: "Konnt d'Zwee-Faktor-Authentifikatioun net ariichten.",
            invalidCode:
                'Ongültege Verifikatiounscode. Probéiert et nach eng Kéier.',
            disableFailed:
                "Konnt d'Zwee-Faktor-Authentifikatioun net desaktivéieren.",
            regenerateFailed: "Konnt d'Recovery-Coden net nei generéieren.",
            loadRecoveryCodes: "Konnt d'Recovery-Coden net lueden.",
            copyFailed: "D'Recovery-Coden konnten net kopéiert ginn.",
        },
    },
    confirmPasswordModal: {
        password: 'Passwuert',
        passwordPlaceholder: 'Gitt Äert Passwuert an',
        cancel: 'Ofbriechen',
        confirm: 'Confirméieren',
        confirming: 'Gëtt verifizéiert...',
        close: 'Zoumaachen',
        enableTwoFactor: {
            title: 'Zwee-Faktor-Auth aktivéieren',
            description:
                'Gitt Äert Passwuert an, fir d’Zwee-Faktor-Authentifikatioun ze aktivéieren.',
            confirm: '2FA aktivéieren',
        },
        disableTwoFactor: {
            title: 'Zwee-Faktor-Authentifikatioun desaktivéieren',
            description:
                'Gitt Äert Passwuert an, fir d’Zwee-Faktor-Authentifikatioun ze desaktivéieren.',
            confirm: '2FA desaktivéieren',
        },
        regenerateCodes: {
            title: 'Recovery-Coden nei generéieren',
            description:
                'Gitt Äert Passwuert an, fir Är Recovery-Coden nei ze generéieren.',
            confirm: 'Coden nei generéieren',
        },
        default: {
            title: 'Passwuert confirméieren',
            description: 'Gitt Äert Passwuert an.',
        },
        errors: {
            required: 'D’Passwuert ass obligatoresch.',
            invalid: 'Ongültegt Passwuert. Probéiert et nach eng Kéier.',
            verificationFailed:
                'D’Passwuert konnt net verifizéiert ginn. Probéiert et nach eng Kéier.',
        },
    },
};
