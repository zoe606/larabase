import { Slot } from '@radix-ui/react-slot';
import { cva, type VariantProps } from 'class-variance-authority';
import * as React from 'react';

import { fieldClasses } from '@/components/ui/input';
import { cn } from '@/lib/utils';

const filled = 'bg-primary text-on-primary hover:shadow-elevation-1';
const tonal = 'bg-secondary-container text-on-secondary-container hover:shadow-elevation-1';
const outlined = 'border border-outline text-primary disabled:border-on-surface/12 disabled:bg-transparent';
const text = 'px-3 text-primary disabled:bg-transparent';
const danger = 'bg-error text-on-error hover:shadow-elevation-1';

const buttonVariants = cva(
    'state-layer touch-target inline-flex h-[var(--md-btn-h)] shrink-0 cursor-pointer items-center justify-center gap-2 whitespace-nowrap rounded-shape-full px-6 text-label-lg transition-shadow duration-200 ease-standard focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring disabled:pointer-events-none disabled:bg-on-surface/12 disabled:text-on-surface/38 disabled:shadow-none [&_svg]:pointer-events-none [&_svg]:size-[18px] [&_svg]:shrink-0',
    {
        variants: {
            variant: {
                filled,
                tonal,
                outlined,
                text,
                elevated: 'bg-surface-container-low text-primary shadow-elevation-1 hover:shadow-elevation-2',
                danger,
                // Looks like an outlined text field. Used by date pickers and comboboxes that open a popover.
                field: `${fieldClasses} h-[var(--md-field-h)] justify-start font-normal before:hidden`,
                link: 'h-auto px-0 text-primary underline-offset-4 before:hidden hover:underline disabled:bg-transparent',
                // Legacy shadcn names kept for existing call sites
                default: filled,
                secondary: tonal,
                outline: outlined,
                ghost: text,
                destructive: danger,
            },
            size: {
                default: '',
                sm: 'h-8 px-4 text-label-md',
                lg: 'h-12 px-8',
                icon: 'size-10 px-0 [&_svg]:size-6',
                'icon-sm': 'size-8 px-0 [&_svg]:size-5',
            },
        },
        compoundVariants: [
            { variant: ['ghost', 'text'], size: ['icon', 'icon-sm'], class: 'text-on-surface-variant' },
            { variant: ['outline', 'outlined'], size: ['icon', 'icon-sm'], class: 'border-outline text-on-surface-variant' },
        ],
        defaultVariants: {
            variant: 'filled',
            size: 'default',
        },
    },
);

export interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement>, VariantProps<typeof buttonVariants> {
    asChild?: boolean;
}

const Button = React.forwardRef<HTMLButtonElement, ButtonProps>(({ className, variant, size, asChild = false, ...props }, ref) => {
    const Comp = asChild ? Slot : 'button';
    return <Comp className={cn(buttonVariants({ variant, size }), className)} ref={ref} {...props} />;
});
Button.displayName = 'Button';

export { Button, buttonVariants };
