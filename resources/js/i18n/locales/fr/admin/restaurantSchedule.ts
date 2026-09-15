export default {
    title: 'Horaires du restaurant',
    description:
        'Gérez les horaires d’ouverture habituels et les fermetures exceptionnelles. Les heures sont affichées dans le fuseau horaire {timezone}.',

    weekdays: {
        monday: 'Lundi',
        tuesday: 'Mardi',
        wednesday: 'Mercredi',
        thursday: 'Jeudi',
        friday: 'Vendredi',
        saturday: 'Samedi',
        sunday: 'Dimanche',
    },

    regular: {
        title: 'Horaires d’ouverture habituels',
        description: 'Les horaires hebdomadaires habituels du restaurant.',
        open: 'Ouvert',
        closed: 'Fermé',
        opensAt: 'Ouverture à',
        closesAt: 'Fermeture à',
        lastPickup: 'Dernier retrait',
        closedAllDay: 'Le restaurant est fermé toute la journée.',
        save: 'Enregistrer les horaires',
        saving: 'Enregistrement…',
        periodNumber: 'Période {number}',
        addPeriod: 'Ajouter une deuxième période',
        removePeriod: 'Supprimer la deuxième période',
    },

    closures: {
        title: 'Fermetures exceptionnelles',
        description:
            'Fermetures pour une journée entière ou une période temporaire.',
        addTitle: 'Ajouter une fermeture exceptionnelle',
        editTitle: 'Modifier la fermeture exceptionnelle',
        addDescription:
            'Fermez le restaurant pendant une ou plusieurs journées complètes, ou sélectionnez une période personnalisée.',
        editDescription:
            'Modifiez la période de fermeture sélectionnée et le message.',
        fullDay: 'Journée entière',
        customHours: 'Horaires personnalisés',
        firstClosedDay: 'Premier jour de fermeture',
        lastClosedDay: 'Dernier jour de fermeture',
        closedFrom: 'Fermé à partir de',
        closedUntil: 'Fermé jusqu’à',
        internalReason: 'Motif interne',
        reasonPlaceholder: 'Par exemple : jour férié',
        reasonHelp: 'Seuls les administrateurs verront ce motif.',
        clientMessage: 'Message client',
        messagePlaceholder:
            'Par exemple : Nous sommes fermés aujourd’hui en raison d’un jour férié.',
        messageHelp:
            'Affiché dans la bannière client et la fenêtre d’avertissement.',
        reset: 'Réinitialiser',
        cancelEditing: 'Annuler la modification',
        adding: 'Ajout de la fermeture…',
        saving: 'Enregistrement des modifications…',
        add: 'Ajouter la fermeture',
        save: 'Enregistrer les modifications',
        empty: 'Aucune fermeture exceptionnelle n’a été ajoutée.',
        fallbackName: 'Fermeture exceptionnelle',
        edit: 'Modifier',
        delete: 'Supprimer',
        clientMessageLabel: 'Message client : {message}',
        createdBy: 'Créée par {name}',
        confirmDelete: 'Voulez-vous vraiment supprimer « {name} » ?',
        confirmDeleteUnnamed:
            'Voulez-vous vraiment supprimer cette fermeture exceptionnelle ?',
    },

    messages: {
        hoursSaved: 'Les horaires d’ouverture ont été enregistrés.',
        hoursValidation:
            'Veuillez corriger les champs des horaires mis en évidence.',
        hoursFailed: 'Impossible d’enregistrer les horaires d’ouverture.',
        closureAdded: 'La fermeture exceptionnelle a été ajoutée.',
        closureUpdated: 'La fermeture exceptionnelle a été mise à jour.',
        closureValidation:
            'Veuillez corriger les champs de fermeture mis en évidence.',
        closureAddFailed: 'Impossible d’ajouter la fermeture exceptionnelle.',
        closureUpdateFailed:
            'Impossible de mettre à jour la fermeture exceptionnelle.',
        closureDeleted: 'La fermeture exceptionnelle a été supprimée.',
        closureDeleteFailed:
            'Impossible de supprimer la fermeture exceptionnelle.',
    },
};
