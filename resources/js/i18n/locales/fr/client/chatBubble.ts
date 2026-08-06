export default {
    title: 'Assistance',
    subtitle: 'Nous sommes là pour vous aider',
    date: { today: "Aujourd'hui", yesterday: 'Hier' },
    labels: {
        modifiedAt: 'Modifié à {time}',
        readAt: 'Lu à {time}',
        editingInstructions:
            'Appuyez sur Entrée pour enregistrer ou sur Échap pour annuler',
    },
    placeholders: { message: 'Saisissez votre message...' },
    emptyStates: {
        noMessages: 'Aucun message pour le moment',
        startConversation: 'Commencez une conversation avec nous !',
    },
    actions: {
        cancel: 'Annuler',
        edit: 'Modifier',
        unsend: "Annuler l'envoi",
        remove: 'Supprimer',
    },
    messages: {
        youUnsent: "Vous avez annulé l'envoi de ce message",
        userUnsent: "{name} a annulé l'envoi de ce message",
        youDeleted: 'Vous avez supprimé ce message',
        userDeleted: '{name} a supprimé ce message',
    },
};
