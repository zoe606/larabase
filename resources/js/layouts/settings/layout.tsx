import { Heading } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { useIsMobile } from '@/hooks/use-mobile';
import { cn } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import { Menu } from 'lucide-react';
import { useState } from 'react';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Profile',
        url: '/settings/profile',
        icon: null,
    },
    {
        title: 'Password',
        url: '/settings/password',
        icon: null,
    },
];

function SettingsNav({ currentPath, onNavigate }: { currentPath: string; onNavigate?: () => void }) {
    return (
        <nav className="flex flex-col space-y-1">
            {sidebarNavItems.map((item) => (
                <Button
                    key={item.url}
                    size="sm"
                    variant="ghost"
                    asChild
                    className={cn('w-full justify-start', {
                        'bg-muted': currentPath === item.url,
                    })}
                    onClick={onNavigate}
                >
                    <Link href={item.url} prefetch>
                        {item.title}
                    </Link>
                </Button>
            ))}
        </nav>
    );
}

export default function SettingsLayout({ children }: { children: React.ReactNode }) {
    const currentPath = window.location.pathname;
    const isMobile = useIsMobile();
    const [sheetOpen, setSheetOpen] = useState(false);

    const currentPageTitle = sidebarNavItems.find((item) => item.url === currentPath)?.title || 'Settings';

    return (
        <div className="px-4 py-6">
            <Heading title="Profile Settings" description="Manage your profile and account settings" />

            <div className="flex flex-col space-y-6 lg:flex-row lg:space-y-0 lg:space-x-12">
                {isMobile ? (
                    <div className="flex items-center gap-3">
                        <Sheet open={sheetOpen} onOpenChange={setSheetOpen}>
                            <SheetTrigger asChild>
                                <Button variant="outline" size="sm" className="h-10 gap-2">
                                    <Menu className="h-4 w-4" />
                                    <span>{currentPageTitle}</span>
                                </Button>
                            </SheetTrigger>
                            <SheetContent side="left" className="w-64">
                                <SheetHeader>
                                    <SheetTitle>Settings</SheetTitle>
                                </SheetHeader>
                                <div className="mt-6">
                                    <SettingsNav currentPath={currentPath} onNavigate={() => setSheetOpen(false)} />
                                </div>
                            </SheetContent>
                        </Sheet>
                    </div>
                ) : (
                    <aside className="w-48 shrink-0">
                        <SettingsNav currentPath={currentPath} />
                    </aside>
                )}

                <div className="min-w-0 flex-1 md:max-w-2xl">
                    <section className="max-w-xl space-y-12">{children}</section>
                </div>
            </div>
        </div>
    );
}
