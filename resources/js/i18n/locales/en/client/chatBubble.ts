export default {
    title: 'Support Chat',
    subtitle: "We're here to help",
    date: { today: 'Today', yesterday: 'Yesterday' },
    labels: {
        modifiedAt: 'Modified at {time}',
        readAt: 'Read at {time}',
        editingInstructions: 'Press Enter to save, Escape to cancel',
    },
    placeholders: { message: 'Type your message...' },
    emptyStates: {
        noMessages: 'No messages yet',
        startConversation: 'Start a conversation with us!',
    },
    actions: {
        cancel: 'Cancel',
        edit: 'Edit',
        unsend: 'Unsend',
        remove: 'Remove',
    },
    messages: {
        youUnsent: 'You unsent this message',
        userUnsent: '{name} unsent this message',
        youDeleted: 'You removed this message',
        userDeleted: '{name} removed this message',
    },
};
