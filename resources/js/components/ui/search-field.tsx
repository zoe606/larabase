import { Search } from 'lucide-react';
import * as React from 'react';

import { cn } from '@/lib/utils';

/** MD3 search bar: a filled pill used at the start of list filter rows. `className` styles the wrapper (layout). */
const SearchField = React.forwardRef<HTMLInputElement, Omit<React.ComponentProps<'input'>, 'type'>>(({ className, ...props }, ref) => (
    <div className={cn('relative min-w-0', className)}>
        <Search aria-hidden className="pointer-events-none absolute top-1/2 left-4 size-5 -translate-y-1/2 text-on-surface-variant" />
        <input
            ref={ref}
            type="search"
            className="h-12 w-full rounded-shape-full bg-surface-container-highest pr-4 pl-12 text-body-lg text-on-surface placeholder:text-on-surface-variant transition-shadow duration-200 ease-standard focus-visible:shadow-[inset_0_0_0_2px_var(--md-primary)] focus-visible:outline-none disabled:cursor-not-allowed disabled:text-on-surface/38 [&::-webkit-search-cancel-button]:hidden"
            {...props}
        />
    </div>
));
SearchField.displayName = 'SearchField';

export { SearchField };
