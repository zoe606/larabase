import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/react';
import { AlertCircle, Inbox, Lock, LucideIcon, PackageOpen, SearchX } from 'lucide-react';
import { ReactNode } from 'react';

type EmptyStateVariant = 'no-data' | 'no-results' | 'error' | 'no-permission';

interface EmptyStateAction {
    label: string;
    href?: string;
    onClick?: () => void;
}

interface EmptyStateProps {
    icon?: LucideIcon;
    title?: string;
    description?: string;
    variant?: EmptyStateVariant;
    action?: ReactNode | EmptyStateAction;
    suggestions?: string[];
    className?: string;
}

const variantConfig: Record<EmptyStateVariant, { icon: LucideIcon; defaultTitle: string; defaultDescription: string }> = {
    'no-data': {
        icon: Inbox,
        defaultTitle: 'No data yet',
        defaultDescription: 'Get started by creating your first item.',
    },
    'no-results': {
        icon: SearchX,
        defaultTitle: 'No results found',
        defaultDescription: 'Try adjusting your search or filters.',
    },
    error: {
        icon: AlertCircle,
        defaultTitle: 'Something went wrong',
        defaultDescription: 'Please try again later.',
    },
    'no-permission': {
        icon: Lock,
        defaultTitle: 'Access denied',
        defaultDescription: "You don't have permission to view this.",
    },
};

function isEmptyStateAction(action: ReactNode | EmptyStateAction): action is EmptyStateAction {
    return typeof action === 'object' && action !== null && 'label' in action;
}

export function EmptyState({ icon, title, description, variant, action, suggestions, className }: EmptyStateProps) {
    // Get variant config or use defaults
    const config = variant ? variantConfig[variant] : null;
    const Icon = icon ?? config?.icon ?? PackageOpen;
    const displayTitle = title ?? config?.defaultTitle ?? 'No data available';
    const displayDescription = description ?? config?.defaultDescription;

    return (
        <div className={cn('flex flex-col items-center justify-center py-12 text-center', className)}>
            <div className="bg-muted mb-4 rounded-full p-4">
                <Icon className="text-muted-foreground h-8 w-8" />
            </div>
            <h3 className="text-lg font-medium">{displayTitle}</h3>
            {displayDescription && <p className="text-muted-foreground mt-1 max-w-sm text-sm">{displayDescription}</p>}

            {suggestions && suggestions.length > 0 && (
                <ul className="text-muted-foreground mt-3 space-y-1 text-sm">
                    {suggestions.map((suggestion, index) => (
                        <li key={index} className="flex items-center justify-center gap-1">
                            <span className="text-muted-foreground/60">•</span>
                            {suggestion}
                        </li>
                    ))}
                </ul>
            )}

            {action && (
                <div className="mt-4">
                    {isEmptyStateAction(action) ? (
                        action.href ? (
                            <Link href={action.href}>
                                <Button>{action.label}</Button>
                            </Link>
                        ) : (
                            <Button onClick={action.onClick}>{action.label}</Button>
                        )
                    ) : (
                        action
                    )}
                </div>
            )}
        </div>
    );
}
