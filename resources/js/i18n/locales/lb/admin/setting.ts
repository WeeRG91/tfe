export default {
    userMenu: {
        settings: 'Astellungen',
        logout: 'Ausloggen',
    },
    layout: {
        title: 'Astellungen',
        description: 'Verwalt Äre Profil an Är Kontastellungen',
        navigation: {
            profile: 'Profil',
            password: 'Passwuert',
            twoFactorAuth: 'Zwee-Faktor-Authentifikatioun',
            appearance: 'Erscheinungsbild',
        },
    },
    profile: {
        title: 'Profilastellungen',
        heading: {
            title: 'Profilinformatiounen',
            description: 'Aktualiséiert Ären Numm an Är E-Mail-Adress',
        },
        fields: { avatar: 'Profilbild', name: 'Numm', email: 'E-Mail-Adress' },
        placeholders: { name: 'Vollen Numm', email: 'E-Mail-Adress' },
        avatar: {
            uploadHint:
                'Klickt op d’Kamera-Ikon, fir en neit Bild eropzelueden',
            requirements: 'JPG, JPEG, PNG, GIF, WEBP. Maximal 2 MB',
        },
        verification: {
            unverified: 'Är E-Mail-Adress ass nach net verifizéiert.',
            resend: 'Klickt hei fir d’Verifizéierungs-E-Mail nach eng Kéier ze schécken.',
            linkSent:
                'En neie Verifizéierungslink gouf op Är E-Mail-Adress geschéckt.',
        },
        messages: { saved: 'Gespäichert.' },
        errors: {
            photoTooLarge: 'D’Foto däerf net méi grouss wéi 2 MB sinn.',
            invalidPhoto:
                'Luet w.e.g. e valabelt Bild erop (JPG, JPEG, PNG, GIF oder WEBP).',
        },
    },
    deleteAccount: {
        title: 'Kont läschen',
        description: 'Läscht Äre Kont an all seng Ressourcen.',
        warning: {
            title: 'Warnung',
            description:
                'Gitt w.e.g. virsiichteg vir. Dës Aktioun kann net réckgängeg gemaach ginn.',
        },
        dialog: {
            title: 'Sidd Dir sécher, datt Dir Äre Kont läsche wëllt?',
            description:
                'Wann Äre Kont geläscht gëtt, ginn och all Är Ressourcen an Donnéeë permanent geläscht. Gitt w.e.g. Äert Passwuert an, fir d’Läsche vun Ärem Kont ze bestätegen.',
        },
        fields: {
            password: 'Passwuert',
        },
        placeholders: {
            password: 'Passwuert',
        },
        buttons: {
            delete: 'Kont läschen',
        },
    },
    password: {
        title: 'Passwuert-Astellungen',
        heading: {
            title: 'Passwuert aktualiséieren',
            description:
                'Benotzt e laangt a séchert Passwuert, fir Äre Kont optimal ze schützen.',
        },
        fields: {
            currentPassword: 'Aktuellt Passwuert',
            newPassword: 'Neit Passwuert',
            confirmPassword: 'Passwuert confirméieren',
        },
        placeholders: {
            currentPassword: 'Aktuellt Passwuert',
            newPassword: 'Neit Passwuert',
            confirmPassword: 'Passwuert confirméieren',
        },
        passwordRequirements: {
            characters: '8+ Zeechen',
            uppercase: 'Grouss Buschtaf',
            lowercase: 'Kleng Buschtaf',
            number: 'Zuel',
            symbol: 'Symbol',
        },
        messages: {
            saved: 'Gespäichert.',
        },
    },
    twoFactorAuth: {
        title: 'Zwee-Faktor-Authentifikatioun',
        heading: {
            title: 'Zwee-Faktor-Authentifikatioun',
            description:
                'Verwalt Är Astellunge fir d’Zwee-Faktor-Authentifikatioun.',
        },
        status: {
            enabled: 'Aktivéiert',
            disabled: 'Desaktivéiert',
        },
        descriptions: {
            disabled:
                'Wann Dir d’Zwee-Faktor-Authentifikatioun aktivéiert, gitt Dir beim Umellen no engem séchere PIN gefrot. Dëse PIN fannt Dir an enger TOTP-kompatibeler App op Ärem Telefon.',
            enabled:
                'Mat aktivéierter Zwee-Faktor-Authentifikatioun gitt Dir bei all Umellung no engem séchere PIN gefrot. Dëse PIN fannt Dir an enger TOTP-kompatibeler App op Ärem Telefon.',
        },
        buttons: {
            continueSetup: 'Konfiguratioun weiderféieren',
            enable: '2FA aktivéieren',
            disable: '2FA desaktivéieren',
        },
    },
    twoFactorRecovery: {
        title: '2FA-Erhuelungscoden',
        description:
            'Mat Erhuelungscode kënnt Dir erëm Zougang zu Ärem Kont kréien, wann Dir Ären 2FA-Apparat verléiert. Späichert se an engem séchere Passwuertmanager.',
        buttons: {
            view: 'Erhuelungscode weisen',
            hide: 'Erhuelungscode verstoppen',
            regenerate: 'Code nei generéieren',
        },
        information: {
            usage: 'All Erhuelungscode kann nëmmen eemol benotzt ginn, fir op Äre Kont zouzegräifen, a gëtt nom Gebrauch geläscht. Wann Dir nei Code braucht, klickt uewen op „Code nei generéieren“.',
        },
    },
    twoFactorModal: {
        enabled: {
            title: 'Zwee-Faktor-Authentifikatioun aktivéiert',
            description:
                'D’Zwee-Faktor-Authentifikatioun ass elo aktivéiert. Scannt de QR-Code oder gitt de Konfiguratiounsschlëssel an Ärer Authentifikatiouns-App an.',
            button: 'Zoumaachen',
        },
        verification: {
            title: 'Authentifikatiounscode iwwerpréiwen',
            description:
                'Gitt de 6-stellege Code aus Ärer Authentifikatiouns-App an.',
            button: 'Weider',
        },
        setup: {
            title: 'Zwee-Faktor-Authentifikatioun aktivéieren',
            description:
                'Fir d’Aktivéierung vun der Zwee-Faktor-Authentifikatioun ofzeschléissen, scannt de QR-Code oder gitt de Konfiguratiounsschlëssel an Ärer Authentifikatiouns-App an.',
            button: 'Weider',
        },
        manualSetup: { separator: 'oder gitt de Code manuell an' },
    },
    appearance: {
        title: 'Erscheinungsastellungen',
        heading: {
            title: 'Erscheinungsastellungen',
            description: 'Ännert d’Erscheinungsastellunge vun Ärem Kont.',
        },
        options: { light: 'Hell', dark: 'Däischter', system: 'System' },
    },
};
