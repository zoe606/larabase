import * as ProgressPrimitive from '@radix-ui/react-progress';
import * as React from 'react';

import { cn } from '@/lib/utils';

const Progress = React.forwardRef<
    React.ElementRef<typeof ProgressPrimitive.Root>,
    React.ComponentPropsWithoutRef<typeof ProgressPrimitive.Root> & { indicatorClassName?: string }
>(
    ({ className, indicatorClassName, value, ...props }, ref) => {
        const pct = Math.min(100, Math.max(0, value ?? 0));
        return (
            <ProgressPrimitive.Root ref={ref} value={pct} className={cn('relative flex h-1 w-full items-center gap-1', className)} {...props}>
                <ProgressPrimitive.Indicator
                    className={cn('h-full rounded-full bg-primary transition-[width] duration-300 ease-standard', indicatorClassName)}
                    style={{ width: `${pct}%` }}
                />
                <span className="relative h-full flex-1 rounded-full bg-secondary-container">
                    <span className="absolute right-0 top-1/2 size-1 -translate-y-1/2 rounded-full bg-primary" aria-hidden />
                </span>
            </ProgressPrimitive.Root>
        );
    },
);
Progress.displayName = ProgressPrimitive.Root.displayName;

export { Progress };
