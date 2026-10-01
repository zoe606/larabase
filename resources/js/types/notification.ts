export interface Notification {
    id: string;
    type_key: string;
    title: string;
    message: string;
    is_read: boolean;
    time_ago: string;
    link: string | null;
    created_at: string;
}

export interface NotificationFilters {
    unread_only: boolean;
    type: string | null;
}

export interface PaginatedNotifications {
    data: Notification[];
    links: {
        first: string | null;
        last: string | null;
        prev: string | null;
        next: string | null;
    };
    meta: {
        current_page: number;
        from: number | null;
        last_page: number;
        per_page: number;
        to: number | null;
        total: number;
    };
}
