import { cn } from '@/lib/utils';
import { Link, usePage } from '@inertiajs/react';
import { Bell } from 'lucide-react';

export function NotificationBadge() {
    const { unreadNotificationCount = 0 } = usePage().props;
    const hasUnread = unreadNotificationCount > 0;

    return (
        <Link
            href="/notifications"
            className={cn(
                'state-layer rounded-shape-full text-on-surface-variant relative inline-flex size-10 items-center justify-center',
                'focus-visible:outline-ring focus-visible:outline-2 focus-visible:outline-offset-2',
                hasUnread && 'text-on-surface',
            )}
            title="Notifications"
            aria-label={hasUnread ? `Notifications, ${unreadNotificationCount} unread` : 'Notifications'}
        >
            <Bell className={cn('size-6 transition-transform', hasUnread && 'animate-[wiggle_0.5s_ease-in-out]')} />
            {hasUnread && (
                <span className="absolute top-1 right-1 flex h-4 min-w-4 items-center justify-center">
                    <span className="rounded-shape-full bg-error/40 absolute inline-flex size-full animate-ping" />
                    <span className="rounded-shape-full bg-error text-label-sm text-on-error relative inline-flex h-4 min-w-4 items-center justify-center px-1">
                        {unreadNotificationCount > 99 ? '99+' : unreadNotificationCount}
                    </span>
                </span>
            )}
        </Link>
    );
}
