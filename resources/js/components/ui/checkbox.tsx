import * as CheckboxPrimitive from '@radix-ui/react-checkbox';
import { Check } from 'lucide-react';
import * as React from 'react';

import { cn } from '@/lib/utils';

const Checkbox = React.forwardRef<React.ElementRef<typeof CheckboxPrimitive.Root>, React.ComponentPropsWithoutRef<typeof CheckboxPrimitive.Root>>(
    ({ className, ...props }, ref) => (
        <CheckboxPrimitive.Root
            ref={ref}
            className={cn(
                'state-layer touch-target peer size-[18px] shrink-0 rounded-[2px] border-2 border-on-surface-variant before:-inset-[11px] before:rounded-full focus-visible:outline-2 focus-visible:outline-offset-[11px] focus-visible:outline-ring disabled:cursor-not-allowed disabled:opacity-38 data-[state=checked]:border-primary data-[state=checked]:bg-primary data-[state=checked]:text-on-primary aria-invalid:border-error',
                className,
            )}
            {...props}
        >
            <CheckboxPrimitive.Indicator className={cn('flex items-center justify-center text-current')}>
                <Check className="size-3.5 stroke-[3]" />
            </CheckboxPrimitive.Indicator>
        </CheckboxPrimitive.Root>
    ),
);
Checkbox.displayName = CheckboxPrimitive.Root.displayName;

export { Checkbox };
