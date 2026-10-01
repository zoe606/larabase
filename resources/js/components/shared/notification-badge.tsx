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
                'relative inline-flex items-center justify-center rounded-md p-2',
                'text-muted-foreground hover:bg-accent hover:text-accent-foreground',
                'transition-colors duration-200',
                hasUnread && 'text-foreground',
            )}
            title="Notifications"
        >
            <Bell className={cn('size-5 transition-transform', hasUnread && 'animate-[wiggle_0.5s_ease-in-out]')} />
            {hasUnread && (
                <span className="absolute -top-0.5 -right-0.5 flex size-5 items-center justify-center">
                    <span className="bg-destructive/40 absolute inline-flex size-full animate-ping rounded-full" />
                    <span className="bg-destructive text-destructive-foreground relative inline-flex size-5 items-center justify-center rounded-full text-[10px] font-bold">
                        {unreadNotificationCount > 99 ? '99+' : unreadNotificationCount}
                    </span>
                </span>
            )}
        </Link>
    );
}
