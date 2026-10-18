import * as SwitchPrimitives from '@radix-ui/react-switch';
import { Check } from 'lucide-react';
import * as React from 'react';

import { cn } from '@/lib/utils';

export interface SwitchProps extends React.ComponentPropsWithoutRef<typeof SwitchPrimitives.Root> {
    showIcon?: boolean;
}

const Switch = React.forwardRef<React.ElementRef<typeof SwitchPrimitives.Root>, SwitchProps>(({ className, showIcon = true, ...props }, ref) => (
    <SwitchPrimitives.Root
        ref={ref}
        className={cn(
            'group touch-target peer inline-flex h-8 w-[52px] shrink-0 cursor-pointer items-center rounded-shape-full border-2 transition-colors duration-200 ease-standard focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring disabled:cursor-not-allowed disabled:opacity-38',
            'data-[state=unchecked]:border-outline data-[state=unchecked]:bg-surface-container-highest',
            'data-[state=checked]:border-primary data-[state=checked]:bg-primary',
            className,
        )}
        {...props}
    >
        <SwitchPrimitives.Thumb
            className={cn(
                'pointer-events-none grid place-items-center rounded-full transition-all duration-200 ease-emphasized',
                'data-[state=unchecked]:ml-1.5 data-[state=unchecked]:size-4 data-[state=unchecked]:bg-outline',
                'data-[state=checked]:ml-[22px] data-[state=checked]:size-6 data-[state=checked]:bg-on-primary',
                'group-active:size-7',
            )}
        >
            {showIcon && props.checked !== false && <Check className="hidden size-4 text-on-primary-container group-data-[state=checked]:block" strokeWidth={3} />}
        </SwitchPrimitives.Thumb>
    </SwitchPrimitives.Root>
));
Switch.displayName = SwitchPrimitives.Root.displayName;

export { Switch };
