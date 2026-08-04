export type ChatType = {
    id: number;
    user: { id: number; name: string; email: string };
    last_message_at: string;
    created_at: string;
    latest_message: MessageType;
};

export type MessageType = {
    id: number;
    chat_id: number;
    sender_name: string;
    is_from_restaurant: boolean;
    content: string;
    read_at: string;
    edited_at: string;
    unsent_at: string;
    created_at: string;
    deleted_at: string;
};
