import { AppContent, AppShell, AppSidebar, AppSidebarHeader } from '@/components/app';
import { SkipNavigation } from '@/components/shared/skip-navigation';
import { Toaster } from '@/components/ui/sonner';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/react';
import { useEffect, useRef } from 'react';
import { toast } from 'sonner';

interface Props {
    children: React.ReactNode;
    breadcrumbs?: BreadcrumbItem[];
    title?: string;
}

export default function AppSidebarLayout({ children, breadcrumbs = [], title = 'Dashboard' }: Props) {
    const { props } = usePage();
    const flashShownRef = useRef<string | null>(null);

    const flash = props.flash ?? {};
    const { setting } = props;

    // Handle flash messages - use ref to prevent duplicates
    useEffect(() => {
        const flashKey = `${flash.success || ''}-${flash.error || ''}`;
        if (flashKey !== '-' && flashShownRef.current !== flashKey) {
            flashShownRef.current = flashKey;
            if (flash.success) toast.success(flash.success);
            if (flash.error) toast.error(flash.error);
        }
    }, [flash.success, flash.error]);

    // Reset flash ref on navigation to allow same message to show again on next action
    useEffect(() => {
        const unsubscribe = router.on('start', () => {
            flashShownRef.current = null;
        });
        return () => unsubscribe();
    }, []);

    const primaryColor = setting?.warna || '#0ea5e9';
    const primaryForeground = '#ffffff';

    return (
        <>
            <SkipNavigation />
            <Head>
                <title>{title ?? setting?.seo?.title ?? setting?.nama_app ?? 'Dashboard'}</title>
                {setting?.seo?.description && <meta name="description" content={setting.seo.description} />}
                {setting?.seo?.keywords && <meta name="keywords" content={setting.seo.keywords} />}
                <style>
                    {`
            :root {
              --primary: ${primaryColor};
              --color-primary: ${primaryColor};
              --primary-foreground: ${primaryForeground};
              --color-primary-foreground: ${primaryForeground};
            }
            .dark {
              --primary: ${primaryColor};
              --color-primary: ${primaryColor};
              --primary-foreground: ${primaryForeground};
              --color-primary-foreground: ${primaryForeground};
            }
          `}
                </style>
            </Head>

            <div
                style={
                    {
                        '--primary': primaryColor,
                        '--primary-foreground': primaryForeground,
                        '--color-primary': primaryColor,
                        '--color-primary-foreground': primaryForeground,
                    } as React.CSSProperties
                }
            >
                <AppShell variant="sidebar">
                    <AppSidebar />
                    <AppContent variant="sidebar">
                        <AppSidebarHeader breadcrumbs={breadcrumbs} />
                        <main id="main-content">{children}</main>
                    </AppContent>
                </AppShell>
            </div>

            <Toaster />
        </>
    );
}
