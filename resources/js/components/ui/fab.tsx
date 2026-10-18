import { type LucideIcon } from 'lucide-react';
import * as React from 'react';

import { cn } from '@/lib/utils';

export interface FabProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
    icon: LucideIcon;
    label?: string;
}

export function fabClassName(extended: boolean): string {
    return cn(
        'state-layer inline-flex items-center justify-center gap-3 rounded-shape-lg bg-primary-container text-on-primary-container shadow-elevation-3 transition-shadow duration-200 ease-standard hover:shadow-elevation-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring',
        extended ? 'h-14 px-4 pr-5 text-label-lg' : 'size-14',
    );
}

const Fab = React.forwardRef<HTMLButtonElement, FabProps>(({ icon: Icon, label, className, type = 'button', ...props }, ref) => (
    <button
        ref={ref}
        type={type}
        className={cn(fabClassName(!!label), className)}
        {...props}
    >
        <Icon className="size-6" aria-hidden />
        {label}
    </button>
));
Fab.displayName = 'Fab';

export { Fab };
