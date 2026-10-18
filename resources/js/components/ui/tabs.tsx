import * as TabsPrimitive from '@radix-ui/react-tabs';
import * as React from 'react';

import { cn } from '@/lib/utils';

type TabsVariant = 'primary' | 'segmented';

const TabsVariantContext = React.createContext<TabsVariant>('primary');

const Tabs = TabsPrimitive.Root;

const TabsList = React.forwardRef<
    React.ElementRef<typeof TabsPrimitive.List>,
    React.ComponentPropsWithoutRef<typeof TabsPrimitive.List> & { variant?: TabsVariant }
>(({ className, variant = 'primary', ...props }, ref) => (
    <TabsVariantContext.Provider value={variant}>
        <TabsPrimitive.List
            ref={ref}
            className={cn(
                variant === 'primary'
                    ? 'flex h-12 items-stretch border-b border-surface-container-highest text-on-surface-variant'
                    : 'inline-flex h-10 items-stretch divide-x divide-outline overflow-hidden rounded-shape-full border border-outline text-on-surface',
                className,
            )}
            {...props}
        />
    </TabsVariantContext.Provider>
));
TabsList.displayName = TabsPrimitive.List.displayName;

const TabsTrigger = React.forwardRef<React.ElementRef<typeof TabsPrimitive.Trigger>, React.ComponentPropsWithoutRef<typeof TabsPrimitive.Trigger>>(
    ({ className, ...props }, ref) => {
        const variant = React.useContext(TabsVariantContext);
        return (
            <TabsPrimitive.Trigger
                ref={ref}
                className={cn(
                    'state-layer inline-flex flex-1 items-center justify-center gap-2 whitespace-nowrap px-4 text-title-sm focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-ring disabled:pointer-events-none disabled:opacity-38 [&_svg]:size-[18px]',
                    variant === 'primary'
                        ? 'relative data-[state=active]:text-primary after:absolute after:inset-x-4 after:bottom-0 after:h-[3px] after:rounded-t-[3px] after:bg-primary after:opacity-0 after:transition-opacity data-[state=active]:after:opacity-100'
                        : 'text-label-lg data-[state=active]:bg-secondary-container data-[state=active]:text-on-secondary-container',
                    className,
                )}
                {...props}
            />
        );
    },
);
TabsTrigger.displayName = TabsPrimitive.Trigger.displayName;

const TabsContent = React.forwardRef<React.ElementRef<typeof TabsPrimitive.Content>, React.ComponentPropsWithoutRef<typeof TabsPrimitive.Content>>(
    ({ className, ...props }, ref) => (
        <TabsPrimitive.Content ref={ref} className={cn('mt-4 focus-visible:outline-2 focus-visible:outline-ring', className)} {...props} />
    ),
);
TabsContent.displayName = TabsPrimitive.Content.displayName;

export { Tabs, TabsContent, TabsList, TabsTrigger };
