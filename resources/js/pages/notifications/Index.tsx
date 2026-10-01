import { EmptyState, PageHeader } from '@/components/shared';
import { DataTablePagination } from '@/components/shared/data-table/pagination';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import type { Notification, PaginatedNotifications } from '@/types/notification';
import { Head, router } from '@inertiajs/react';
import { AlertCircle, Bell, BellOff, Calendar, Check, CheckCheck, FileText, Info, MessageSquare, User } from 'lucide-react';
import { useCallback, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Notifications',
        href: '/notifications',
    },
];

interface Props {
    notifications: PaginatedNotifications;
    filters: {
        unread_only: boolean;
    };
    unreadNotificationCount: number;
}

type FilterType = 'all' | 'unread';

const notificationIcons: Record<string, typeof Bell> = {
    // General
    default: Bell,
    info: Info,
    alert: AlertCircle,
    message: MessageSquare,

    // User related
    user: User,
    user_created: User,
    user_updated: User,

    // Calendar/Events
    calendar: Calendar,
    event: Calendar,
    reminder: Calendar,

    // Documents
    document: FileText,
    file: FileText,
};

function getNotificationIcon(typeKey: string) {
    // Try to match the type key to an icon
    const key = typeKey.toLowerCase();

    for (const [iconKey, Icon] of Object.entries(notificationIcons)) {
        if (key.includes(iconKey)) {
            return Icon;
        }
    }

    return notificationIcons.default ?? Bell;
}

function NotificationItem({ notification, onMarkAsRead }: { notification: Notification; onMarkAsRead: (id: string) => void }) {
    const Icon = getNotificationIcon(notification.type_key);

    const handleClick = () => {
        if (!notification.is_read) {
            onMarkAsRead(notification.id);
        }

        if (notification.link) {
            router.visit(notification.link);
        }
    };

    return (
        <div
            className={cn(
                'group flex items-start gap-4 px-4 py-4 transition-colors',
                'hover:bg-muted/50 cursor-pointer',
                !notification.is_read && 'bg-primary/5',
            )}
            onClick={handleClick}
            role="button"
            tabIndex={0}
            onKeyDown={(e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    handleClick();
                }
            }}
        >
            {/* Icon */}
            <div
                className={cn(
                    'flex h-10 w-10 shrink-0 items-center justify-center rounded-full',
                    notification.is_read ? 'bg-muted' : 'bg-primary/10',
                )}
            >
                <Icon className={cn('h-5 w-5', notification.is_read ? 'text-muted-foreground' : 'text-primary')} />
            </div>

            {/* Content */}
            <div className="min-w-0 flex-1">
                <div className="flex items-start justify-between gap-2">
                    <div className="min-w-0 flex-1">
                        <p className={cn('truncate text-sm', !notification.is_read && 'font-semibold')}>{notification.title}</p>
                        <p className="text-muted-foreground mt-0.5 line-clamp-2 text-sm">{notification.message}</p>
                        <p className="text-muted-foreground mt-1 text-xs">{notification.time_ago}</p>
                    </div>

                    {/* Mark as read button */}
                    {!notification.is_read && (
                        <Button
                            variant="ghost"
                            size="icon"
                            className="h-8 w-8 shrink-0 opacity-0 transition-opacity group-hover:opacity-100"
                            onClick={(e) => {
                                e.stopPropagation();
                                onMarkAsRead(notification.id);
                            }}
                            title="Mark as read"
                        >
                            <Check className="h-4 w-4" />
                        </Button>
                    )}
                </div>
            </div>

            {/* Unread indicator */}
            {!notification.is_read && <div className="bg-primary mt-2 h-2 w-2 shrink-0 rounded-full" />}
        </div>
    );
}

export default function NotificationsIndex({ notifications, filters, unreadNotificationCount }: Props) {
    const [activeFilter, setActiveFilter] = useState<FilterType>(filters.unread_only ? 'unread' : 'all');

    const handleFilterChange = useCallback((filter: FilterType) => {
        setActiveFilter(filter);
        router.get('/notifications', { unread_only: filter === 'unread' ? '1' : '' }, { preserveState: true, preserveScroll: true });
    }, []);

    const handleMarkAsRead = useCallback((id: string) => {
        router.post(`/notifications/${id}/read`, {}, { preserveScroll: true });
    }, []);

    const handleMarkAllAsRead = useCallback(() => {
        router.post('/notifications/mark-all-read', {}, { preserveScroll: true });
    }, []);

    // Transform pagination data to match DataTablePagination interface
    const paginationData = {
        current_page: notifications.meta.current_page,
        last_page: notifications.meta.last_page,
        per_page: notifications.meta.per_page,
        total: notifications.meta.total,
        links: [
            {
                url: notifications.links.prev,
                label: '&laquo; Previous',
                active: false,
            },
            ...Array.from({ length: notifications.meta.last_page }, (_, i) => ({
                url: `/notifications?page=${i + 1}${activeFilter === 'unread' ? '&unread_only=1' : ''}`,
                label: String(i + 1),
                active: i + 1 === notifications.meta.current_page,
            })),
            {
                url: notifications.links.next,
                label: 'Next &raquo;',
                active: false,
            },
        ],
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Notifications" />

            <div className="space-y-6 p-4 md:p-6">
                <PageHeader
                    title="Notifications"
                    description={`You have ${unreadNotificationCount} unread notification${unreadNotificationCount !== 1 ? 's' : ''}`}
                    action={
                        unreadNotificationCount > 0 && (
                            <Button variant="outline" onClick={handleMarkAllAsRead} className="w-full md:w-auto">
                                <CheckCheck className="mr-2 h-4 w-4" />
                                Mark all as read
                            </Button>
                        )
                    }
                />

                <Card>
                    <CardHeader className="border-b pb-4">
                        <div className="flex gap-2">
                            <Button variant={activeFilter === 'all' ? 'default' : 'outline'} size="sm" onClick={() => handleFilterChange('all')}>
                                <Bell className="mr-2 h-4 w-4" />
                                All
                            </Button>
                            <Button
                                variant={activeFilter === 'unread' ? 'default' : 'outline'}
                                size="sm"
                                onClick={() => handleFilterChange('unread')}
                            >
                                <BellOff className="mr-2 h-4 w-4" />
                                Unread
                                {unreadNotificationCount > 0 && (
                                    <span className="ml-2 rounded-full bg-red-500 px-1.5 py-0.5 text-xs text-white">
                                        {unreadNotificationCount > 99 ? '99+' : unreadNotificationCount}
                                    </span>
                                )}
                            </Button>
                        </div>
                    </CardHeader>

                    <CardContent className="p-0">
                        {notifications.data.length === 0 ? (
                            <EmptyState
                                icon={Bell}
                                title={activeFilter === 'unread' ? 'No unread notifications' : 'No notifications yet'}
                                description={
                                    activeFilter === 'unread'
                                        ? 'You have read all your notifications.'
                                        : 'You will receive notifications about important events here.'
                                }
                                action={
                                    activeFilter === 'unread' && (
                                        <Button variant="outline" size="sm" onClick={() => handleFilterChange('all')}>
                                            View all notifications
                                        </Button>
                                    )
                                }
                                className="py-16"
                            />
                        ) : (
                            <div className="divide-y">
                                {notifications.data.map((notification) => (
                                    <NotificationItem key={notification.id} notification={notification} onMarkAsRead={handleMarkAsRead} />
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Pagination */}
                {notifications.meta.last_page > 1 && <DataTablePagination pagination={paginationData} />}
            </div>
        </AppLayout>
    );
}
