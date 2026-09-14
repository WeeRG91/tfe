export default {
    title: 'Restaurant Ëffnungszäiten',
    description:
        'Verwalt déi regulär Ëffnungszäiten an aussergewéinlech Zoumaachungen. D’Zäite ginn an der Zäitzon {timezone} ugewisen.',

    weekdays: {
        monday: 'Méindeg',
        tuesday: 'Dënschdeg',
        wednesday: 'Mëttwoch',
        thursday: 'Donneschdeg',
        friday: 'Freideg',
        saturday: 'Samschdeg',
        sunday: 'Sonndeg',
    },

    regular: {
        title: 'Regulär Ëffnungszäiten',
        description: 'Déi normal wöchentlech Ëffnungszäite vum Restaurant.',
        open: 'Op',
        closed: 'Zou',
        opensAt: 'Mécht op um',
        closesAt: 'Mécht zou um',
        lastPickup: 'Lescht Ofhuelung',
        closedAllDay: 'De Restaurant ass de ganzen Dag zou.',
        save: 'Ëffnungszäite späicheren',
        saving: 'Gëtt gespäichert…',
    },

    closures: {
        title: 'Aussergewéinlech Zoumaachungen',
        description:
            'Zoumaachunge fir e ganzen Dag oder fir eng temporär Period.',
        addTitle: 'Aussergewéinlech Zoumaachung derbäisetzen',
        editTitle: 'Aussergewéinlech Zoumaachung änneren',
        addDescription:
            'Maacht de Restaurant fir een oder méi ganz Deeg zou oder wielt eng individuell Period.',
        editDescription:
            'Ännert déi ausgewielte Zoumaachungsperiod an de Message.',
        fullDay: 'Ganzen Dag',
        customHours: 'Benotzerdefinéiert Zäiten',
        firstClosedDay: 'Éischten zouenen Dag',
        lastClosedDay: 'Leschten zouenen Dag',
        closedFrom: 'Zou vun',
        closedUntil: 'Zou bis',
        internalReason: 'Internen Grond',
        reasonPlaceholder: 'Zum Beispill: Feierdag',
        reasonHelp: 'Nëmmen Administrateure gesinn dëse Grond.',
        clientMessage: 'Message fir de Client',
        messagePlaceholder:
            'Zum Beispill: Mir sinn haut wéinst engem Feierdag zou.',
        messageHelp:
            'Gëtt am Client-Banner an an der Warnungsfënster ugewisen.',
        reset: 'Zrécksetzen',
        cancelEditing: 'Ännerung ofbriechen',
        adding: 'Zoumaachung gëtt bäigesat…',
        saving: 'Ännerunge ginn gespäichert…',
        add: 'Zoumaachung derbäisetzen',
        save: 'Ännerunge späicheren',
        empty: 'Et goufe keng aussergewéinlech Zoumaachungen derbäigesat.',
        fallbackName: 'Aussergewéinlech Zoumaachung',
        edit: 'Änneren',
        delete: 'Läschen',
        clientMessageLabel: 'Message fir de Client: {message}',
        createdBy: 'Erstallt vun {name}',
        confirmDelete: 'Sidd Dir sécher, datt Dir „{name}“ läsche wëllt?',
        confirmDeleteUnnamed:
            'Sidd Dir sécher, datt Dir dës aussergewéinlech Zoumaachung läsche wëllt?',
    },

    messages: {
        hoursSaved: 'Déi regulär Ëffnungszäite goufe gespäichert.',
        hoursValidation:
            'Korrigéiert w.e.g. déi markéiert Felder vun den Ëffnungszäiten.',
        hoursFailed: 'Déi regulär Ëffnungszäite konnten net gespäichert ginn.',
        closureAdded: 'Déi aussergewéinlech Zoumaachung gouf bäigesat.',
        closureUpdated: 'Déi aussergewéinlech Zoumaachung gouf aktualiséiert.',
        closureValidation:
            'Korrigéiert w.e.g. déi markéiert Felder vun der Zoumaachung.',
        closureAddFailed:
            'Déi aussergewéinlech Zoumaachung konnt net bäigesat ginn.',
        closureUpdateFailed:
            'Déi aussergewéinlech Zoumaachung konnt net aktualiséiert ginn.',
        closureDeleted: 'Déi aussergewéinlech Zoumaachung gouf geläscht.',
        closureDeleteFailed:
            'Déi aussergewéinlech Zoumaachung konnt net geläscht ginn.',
    },
};
