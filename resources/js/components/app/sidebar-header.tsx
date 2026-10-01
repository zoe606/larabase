import { Breadcrumbs } from '@/components/navigation/breadcrumbs';
import { AppearanceDropdown } from '@/components/shared/appearance-select';
import { NotificationBadge } from '@/components/shared/notification-badge';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { type BreadcrumbItem as BreadcrumbItemType } from '@/types';

export function AppSidebarHeader({ breadcrumbs = [] }: { breadcrumbs?: BreadcrumbItemType[] }) {
    return (
        <header className="border-sidebar-border/50 flex h-16 shrink-0 items-center justify-between border-b px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4">
            {/* Left: Sidebar + Breadcrumb */}
            <div className="flex items-center gap-2">
                <SidebarTrigger className="-ml-1" />
                <Breadcrumbs breadcrumbs={breadcrumbs} />
            </div>

            {/* Right: Notifications + Theme */}
            <div className="flex items-center gap-2">
                <NotificationBadge />
                <AppearanceDropdown />
            </div>
        </header>
    );
}
