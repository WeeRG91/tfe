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
        options: {
            light: 'Hell',
            dark: 'Däischter',
            system: 'System',
            systemDescription:
                'Automatesch dem hellen oder däischteren Erscheinungsbild vun dësem Apparat suivéieren.',
        },
        restaurantDefault: {
            title: 'Standardtheema vum Restaurant',
            description:
                "Wielt d'Theema, dat Clienten a Gäscht gesinn, bis si hir eegen Erscheinung auswielen.",
            save: 'Standardtheema späicheren',
            saved: "D'Standardtheema fir Clientë gouf aktualiséiert.",
        },
        customThemes: {
            title: 'Benotzerdefinéiert Themen',
            description:
                'Start mat engem agebaute Thema. Äert neit Thema bleift privat, bis et verëffentlecht gëtt.',
            name: 'Numm vum Thema',
            baseTheme: 'Start mat',
            create: 'Entworf erstellen',
            created: 'Thema-Entworf gouf erstallt.',
            published: 'Verëffentlecht',
            draft: 'Entworf',
            empty: 'Nach keng personaliséiert Themen.',
            saved: 'Thema gespäichert.',
            surfaceColors: 'Fortgeschratt: Flächen an Text',
            feedbackColors: 'Fortgeschratt: Statusfaarwen',
            sidebarColors: 'Fortgeschratt: Säiteleesch',
            chartColors: 'Fortgeschratt: Diagrammer',
            publish: 'Thema verëffentlechen',
            saveBeforePublish:
                'Späichert Är Ännerungen, ier Dir verëffentlecht.',
            publishFailed:
                'Dëst Thema kann nach net verëffentlecht ginn. Korrigéiert déi markéiert Faarwen.',
            publishedSuccess:
                'Thema verëffentlecht. D’Clientë kënnen et elo auswielen.',
            delete: 'Theema läschen',
            deleteConfirmation:
                'Soll "{name}" geläscht ginn? Et verschwënnt aus der Theema-Auswiel. Wann et de Standardtheema vum Restaurant ass, gëtt Hell benotzt. Leit, déi et perséinlech gewielt hunn, kréien hir Standardauswiel.',
            deleted: 'Theema geläscht.',
            colors: {
                primary: 'Primär',
                primaryForeground: 'Text op der Primärfaarf',
                secondary: 'Sekundär',
                secondaryForeground: 'Text op der Sekundärfaarf',
                accent: 'Akzent',
                accentForeground: 'Text op der Akzentfaarf',
                background: 'Säitenhannergrond',
                foreground: 'Säitentext',
                card: 'Kaartenhannergrond',
                cardForeground: 'Kaartentext',
                popover: 'Popover-Hannergrond',
                popoverForeground: 'Popover-Text',
                muted: 'Ofgeschwächten Hannergrond',
                mutedForeground: 'Ofgeschwächten Text',
                border: 'Rand',
                input: 'Rand vum Input-Feld',
                ring: 'Focus-Rand',
                destructive: 'Feeler',
                destructiveForeground: 'Text op Feeler',
                success: 'Erfolleg',
                successForeground: 'Text op Erfolleg',
                warning: 'Warnung',
                warningForeground: 'Text op Warnung',
                info: 'Informatioun',
                infoForeground: 'Text op Informatioun',
                sidebarBackground: 'Hannergrond vun der Säiteleesch',
                sidebarForeground: 'Text vun der Säiteleesch',
                sidebarPrimary: 'Primärfaarf vun der Säiteleesch',
                sidebarPrimaryForeground:
                    'Text op der Primärfaarf vun der Säiteleesch',
                sidebarAccent: 'Akzentfaarf vun der Säiteleesch',
                sidebarAccentForeground:
                    'Text op der Akzentfaarf vun der Säiteleesch',
                sidebarBorder: 'Rand vun der Säiteleesch',
                sidebarRing: 'Focus-Rand vun der Säiteleesch',
                chart1: 'Diagramm 1',
                chart2: 'Diagramm 2',
                chart3: 'Diagramm 3',
                chart4: 'Diagramm 4',
                chart5: 'Diagramm 5',
            },
        },
    },
};
