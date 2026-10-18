import { Check, type LucideIcon } from 'lucide-react';
import * as React from 'react';

import { cn } from '@/lib/utils';

export interface ChipProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
    variant?: 'assist' | 'filter';
    selected?: boolean;
    icon?: LucideIcon;
}

const Chip = React.forwardRef<HTMLButtonElement, ChipProps>(({ variant = 'assist', selected = false, icon: Icon, className, children, type = 'button', ...props }, ref) => {
    const isFilter = variant === 'filter';
    const LeadingIcon = isFilter && selected ? Check : Icon;

    return (
        <button
            ref={ref}
            type={type}
            aria-pressed={isFilter ? selected : undefined}
            className={cn(
                'state-layer touch-target inline-flex h-8 items-center gap-2 rounded-shape-sm px-4 text-label-lg transition-colors duration-200 ease-standard focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring disabled:opacity-38 [&_svg]:size-[18px]',
                isFilter && selected ? 'bg-secondary-container text-on-secondary-container' : 'border border-outline text-on-surface-variant',
                LeadingIcon && 'pl-2',
                className,
            )}
            {...props}
        >
            {LeadingIcon && <LeadingIcon aria-hidden className={cn(!isFilter && 'text-primary')} />}
            {children}
        </button>
    );
});
Chip.displayName = 'Chip';

export { Chip };
