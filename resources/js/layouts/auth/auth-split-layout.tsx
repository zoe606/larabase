import { AppLogoIcon } from '@/components/app';
import AppearanceTabs from '@/components/shared/appearance-tabs';
import { Link, usePage } from '@inertiajs/react';
import { PieChart, Shield, TrendingUp, Zap } from 'lucide-react';
import { useEffect } from 'react';

interface AuthLayoutProps {
    children: React.ReactNode;
    title?: string;
    description?: string;
}

const features = [
    { icon: TrendingUp, text: 'Authentication and profiles' },
    { icon: Shield, text: 'Secure and private' },
    { icon: Zap, text: 'Audit logs and file management' },
    { icon: PieChart, text: 'REST API and automated tests' },
];

export default function AuthSplitLayout({ children, title, description }: AuthLayoutProps) {
    const { quote, setting } = usePage().props;

    const primaryColor = setting?.warna || '#0ea5e9';
    const primaryForeground = '#ffffff';

    useEffect(() => {
        document.documentElement.style.setProperty('--primary', primaryColor);
        document.documentElement.style.setProperty('--color-primary', primaryColor);
        document.documentElement.style.setProperty('--primary-foreground', primaryForeground);
        document.documentElement.style.setProperty('--color-primary-foreground', primaryForeground);
    }, [primaryColor, primaryForeground]);

    return (
        <div className="relative grid min-h-dvh lg:grid-cols-2">
            {/* Left panel — branded (desktop only) */}
            <div className="relative hidden flex-col justify-between overflow-hidden bg-gradient-to-br from-gray-900 via-gray-900 to-[var(--primary)]/30 p-10 text-white lg:flex">
                {/* Decorative orbs */}
                <div className="pointer-events-none absolute inset-0">
                    <div className="absolute top-1/4 -left-20 h-64 w-64 rounded-full bg-[var(--primary)]/10 blur-3xl" />
                    <div className="absolute right-0 bottom-1/4 h-48 w-48 rounded-full bg-[var(--primary)]/15 blur-3xl" />
                </div>

                {/* Top — logo */}
                <Link href={route('home')} className="relative z-10 flex items-center gap-2.5">
                    <AppLogoIcon className="h-9 w-9 fill-current text-white" />
                    <span className="text-lg font-semibold">{setting?.nama_app ?? 'Larabase'}</span>
                </Link>

                {/* Center — feature bullets */}
                <div className="relative z-10 space-y-5">
                    {features.map(({ icon: Icon, text }) => (
                        <div key={text} className="flex items-center gap-3">
                            <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/10">
                                <Icon className="h-4.5 w-4.5" />
                            </div>
                            <span className="text-sm text-gray-200">{text}</span>
                        </div>
                    ))}
                </div>

                {/* Bottom — quote */}
                {quote && (
                    <blockquote className="relative z-10 space-y-2">
                        <p className="text-lg leading-relaxed">&ldquo;{quote.message}&rdquo;</p>
                        <footer className="text-sm text-gray-400">{quote.author}</footer>
                    </blockquote>
                )}
            </div>

            {/* Right panel — form area */}
            <div className="bg-background flex flex-col items-center justify-center px-6 py-12 sm:px-8">
                {/* Theme toggle (top-right corner) */}
                <div className="absolute top-4 right-4">
                    <AppearanceTabs />
                </div>

                <div className="w-full max-w-sm space-y-6">
                    {/* Mobile logo (hidden on desktop since left panel has it) */}
                    <Link href={route('home')} className="flex items-center justify-center gap-2 lg:hidden">
                        <AppLogoIcon className="h-10 w-10 fill-current text-[var(--primary)]" />
                    </Link>

                    {/* Title */}
                    <div className="flex flex-col items-start gap-2 text-left sm:items-center sm:text-center">
                        <h1 className="text-xl font-medium">{title}</h1>
                        <p className="text-muted-foreground text-sm text-balance">{description}</p>
                    </div>

                    {/* Form */}
                    {children}
                </div>
            </div>
        </div>
    );
}
