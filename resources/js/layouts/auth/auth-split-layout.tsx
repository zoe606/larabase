import { AppLogoIcon } from '@/components/app';
import ThemeToggle from '@/components/shared/theme-toggle';
import { Link, usePage } from '@inertiajs/react';
import { Code, FileText, KeyRound, type LucideIcon } from 'lucide-react';

interface AuthLayoutProps {
    children: React.ReactNode;
    title?: string;
    description?: string;
}

const HIGHLIGHTS: { icon: LucideIcon; label: string }[] = [
    { icon: KeyRound, label: 'Authentication, roles and profiles' },
    { icon: FileText, label: 'Audit logs and file management' },
    { icon: Code, label: 'REST API and automated tests' },
];

export default function AuthSplitLayout({ children, title, description }: AuthLayoutProps) {
    const { quote, setting } = usePage().props;
    const appName = setting?.nama_app ?? 'Larabase';

    return (
        <div className="relative grid min-h-dvh lg:grid-cols-2">
            {/* Branded panel, desktop only */}
            <div className="bg-primary-container text-on-primary-container relative hidden flex-col overflow-hidden p-10 lg:flex">
                <div aria-hidden className="pointer-events-none absolute inset-0">
                    <div className="bg-surface/35 absolute -top-24 -right-24 size-96 rounded-full" />
                    <div className="bg-tertiary-container/80 rounded-shape-xl absolute -right-12 -bottom-12 size-48 -rotate-12" />
                </div>

                <Link href={route('home')} className="relative flex items-center gap-2.5">
                    <AppLogoIcon className="h-9 w-9 fill-current" />
                    <span className="text-title-lg">{appName}</span>
                </Link>

                <div className="relative my-auto py-10">
                    <ul aria-label="Highlights" className="flex flex-col gap-3">
                        {HIGHLIGHTS.map(({ icon: Icon, label }) => (
                            <li key={label} className="text-body-lg flex items-center gap-3">
                                <span className="bg-primary text-on-primary flex size-8 shrink-0 items-center justify-center rounded-full [&_svg]:size-4">
                                    <Icon aria-hidden />
                                </span>
                                {label}
                            </li>
                        ))}
                    </ul>
                </div>

                {quote && (
                    <blockquote className="relative flex flex-col gap-2">
                        <p className="text-body-lg">&ldquo;{quote.message}&rdquo;</p>
                        <footer className="text-body-md text-on-primary-container/80">{quote.author}</footer>
                    </blockquote>
                )}
            </div>

            {/* Form area */}
            <div className="bg-surface-container-low dark:bg-surface relative flex flex-col items-center justify-center px-4 py-12 sm:px-8">
                <div className="absolute top-3 right-3">
                    <ThemeToggle />
                </div>

                <div className="w-full max-w-md">
                    <Link href={route('home')} className="text-primary mb-6 flex items-center justify-center gap-2 lg:hidden">
                        <AppLogoIcon className="h-10 w-10 fill-current" />
                        <span className="text-title-lg text-on-surface">{appName}</span>
                    </Link>

                    <div
                        data-slot="auth-card"
                        className="bg-card shadow-elevation-1 rounded-shape-xl dark:bg-surface-container-high dark:border-outline-variant p-6 sm:p-8 dark:border"
                    >
                        <div className="mb-6 flex flex-col gap-1">
                            <h1 className="text-headline-sm">{title}</h1>
                            <p className="text-body-md text-on-surface-variant">{description}</p>
                        </div>
                        {children}
                    </div>
                </div>
            </div>
        </div>
    );
}
