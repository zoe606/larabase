import { SidebarProvider } from '@/components/ui/sidebar';
import { useIsTablet } from '@/hooks/use-mobile';
import { useState } from 'react';

interface AppShellProps {
    children: React.ReactNode;
    variant?: 'header' | 'sidebar';
}

export function AppShell({ children, variant = 'header' }: AppShellProps) {
    const [isOpen, setIsOpen] = useState(() => (typeof window !== 'undefined' ? localStorage.getItem('sidebar') !== 'false' : true));
    // Tablets start with the rail so the content keeps its width. Opening the drawer there is not saved,
    // so it does not change the desktop preference.
    const isTablet = useIsTablet();
    const [tabletOpen, setTabletOpen] = useState(false);

    const handleSidebarChange = (open: boolean) => {
        setIsOpen(open);

        if (typeof window !== 'undefined') {
            localStorage.setItem('sidebar', String(open));
        }
    };

    if (variant === 'header') {
        return <div className="flex min-h-screen w-full flex-col">{children}</div>;
    }

    return (
        <SidebarProvider defaultOpen={isOpen} open={isTablet ? tabletOpen : isOpen} onOpenChange={isTablet ? setTabletOpen : handleSidebarChange}>
            {children}
        </SidebarProvider>
    );
}
