import * as React from 'react';

import { cn } from '@/lib/utils';

export const fieldClasses =
    'w-full rounded-shape-md border border-outline bg-transparent px-4 text-body-lg text-on-surface placeholder:text-on-surface-variant transition-[border-color,box-shadow] duration-200 ease-standard hover:border-on-surface focus-visible:outline-none focus-visible:border-primary focus-visible:shadow-[inset_0_0_0_1px_var(--md-primary)] aria-invalid:border-error aria-invalid:focus-visible:shadow-[inset_0_0_0_1px_var(--md-error)] disabled:cursor-not-allowed disabled:border-on-surface/12 disabled:text-on-surface/38';

const Input = React.forwardRef<HTMLInputElement, React.ComponentProps<'input'>>(({ className, type, ...props }, ref) => {
    return (
        <input
            type={type}
            className={cn(
                fieldClasses,
                'flex h-[var(--md-field-h)] py-2 file:border-0 file:bg-transparent file:text-label-lg file:text-on-surface',
                className,
            )}
            ref={ref}
            {...props}
        />
    );
});

Input.displayName = 'Input';

export { Input };
