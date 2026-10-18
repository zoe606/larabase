import { AppearanceDropdown } from '@/components/shared/appearance-select';
import { NotificationBadge } from '@/components/shared/notification-badge';
import { UserMenuContent } from '@/components/shared/user-menu-content';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { useSidebar } from '@/components/ui/sidebar';
import { useScrolled } from '@/hooks/use-scrolled';
import { getInitials } from '@/lib/initials';
import { resolveTopAppBar } from '@/lib/top-app-bar';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { ArrowLeft, Menu } from 'lucide-react';

export function AppTopBar({ breadcrumbs = [] }: { breadcrumbs?: BreadcrumbItem[] }) {
    const { auth } = usePage().props;
    const { toggleSidebar } = useSidebar();
    const scrolled = useScrolled();
    const { title, backHref } = resolveTopAppBar(breadcrumbs);

    return (
        <header
            data-scrolled={scrolled}
            className={cn(
                'bg-surface ease-standard sticky top-0 z-20 flex h-16 shrink-0 items-center gap-1 px-2 transition-colors duration-200 md:px-4',
                scrolled && 'bg-surface-container',
            )}
        >
            <Button variant="text" size="icon" className="md:hidden" aria-label="Open navigation" onClick={toggleSidebar}>
                <Menu />
            </Button>
            {backHref && (
                <Button variant="text" size="icon" asChild>
                    <Link href={backHref} aria-label="Back">
                        <ArrowLeft />
                    </Link>
                </Button>
            )}

            <h1 className="text-title-lg text-on-surface ml-1 min-w-0 flex-1 truncate">{title}</h1>

            <div className="flex shrink-0 items-center gap-1">
                <NotificationBadge />
                <AppearanceDropdown />
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button variant="text" size="icon" aria-label="Account menu">
                            <Avatar className="rounded-shape-full size-8 overflow-hidden">
                                <AvatarImage src={auth.user.avatar} alt={auth.user.name} />
                                <AvatarFallback className="rounded-shape-full bg-primary-container text-label-lg text-on-primary-container">
                                    {getInitials(auth.user.name)}
                                </AvatarFallback>
                            </Avatar>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent className="w-64" align="end">
                        <UserMenuContent user={auth.user} />
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </header>
    );
}
