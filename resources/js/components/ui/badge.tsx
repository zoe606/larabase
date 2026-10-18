import { cva, type VariantProps } from 'class-variance-authority';
import * as React from 'react';

import { cn } from '@/lib/utils';

const badgeVariants = cva('inline-flex h-6 items-center gap-1 whitespace-nowrap rounded-shape-sm px-2 text-label-md [&_svg]:size-3.5', {
    variants: {
        variant: {
            default: 'bg-secondary-container text-on-secondary-container',
            secondary: 'bg-surface-container-high text-on-surface-variant',
            destructive: 'bg-error-container text-on-error-container',
            outline: 'border border-outline-variant text-on-surface-variant',
            success: 'bg-success-container text-on-success-container',
            warning: 'bg-warning-container text-on-warning-container',
            info: 'bg-info-container text-on-info-container',
        },
    },
    defaultVariants: { variant: 'default' },
});

export interface BadgeProps extends React.HTMLAttributes<HTMLDivElement>, VariantProps<typeof badgeVariants> {}

function Badge({ className, variant, ...props }: BadgeProps) {
    return <div className={cn(badgeVariants({ variant }), className)} {...props} />;
}

export { Badge, badgeVariants };
