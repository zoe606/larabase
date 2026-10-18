import { buttonVariants } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import * as React from 'react';
import { DayPicker } from 'react-day-picker';

export type CalendarProps = React.ComponentProps<typeof DayPicker>;

function Calendar({ className, classNames, showOutsideDays = true, ...props }: CalendarProps) {
    return (
        <DayPicker
            showOutsideDays={showOutsideDays}
            className={cn('p-3', className)}
            classNames={{
                months: 'flex flex-col sm:flex-row space-y-4 sm:space-x-4 sm:space-y-0',
                month: 'relative space-y-4',
                month_caption: 'flex justify-center pt-1 items-center',
                caption_label: 'text-sm font-medium',
                nav: 'absolute top-0 left-0 right-0 flex justify-between items-center pointer-events-none z-10',
                button_previous: cn(
                    buttonVariants({ variant: 'outline' }),
                    'h-9 w-9 min-h-[44px] min-w-[44px] bg-transparent p-0 opacity-70 hover:opacity-100 pointer-events-auto',
                ),
                button_next: cn(
                    buttonVariants({ variant: 'outline' }),
                    'h-9 w-9 min-h-[44px] min-w-[44px] bg-transparent p-0 opacity-70 hover:opacity-100 pointer-events-auto',
                ),
                month_grid: 'w-full border-collapse space-y-1',
                weekdays: 'flex',
                weekday: 'text-on-surface-variant w-9 text-body-sm',
                week: 'flex w-full mt-2',
                day: cn(
                    'relative p-0 text-center text-sm focus-within:relative focus-within:z-20 [&:has([aria-selected])]:bg-secondary-container [&:has([aria-selected].day-outside)]:bg-secondary-container/50 [&:has([aria-selected].day-range-end)]:rounded-r-md',
                    props.mode === 'range'
                        ? '[&:has(>.day-range-end)]:rounded-r-md [&:has(>.day-range-start)]:rounded-l-md first:[&:has([aria-selected])]:rounded-l-md last:[&:has([aria-selected])]:rounded-r-md'
                        : '[&:has([aria-selected])]:rounded-md',
                ),
                day_button: cn(
                    buttonVariants({ variant: 'ghost' }),
                    'h-9 w-9 p-0 font-normal aria-selected:opacity-100',
                ),
                range_end: 'day-range-end',
                range_start: 'day-range-start',
                selected:
                    'rounded-full bg-primary text-on-primary hover:bg-primary focus:bg-primary',
                today: 'rounded-full border border-primary text-primary',
                outside:
                    'day-outside text-on-surface-variant aria-selected:bg-secondary-container/50 aria-selected:text-on-surface-variant',
                disabled: 'text-on-surface-variant opacity-50',
                range_middle: 'aria-selected:bg-secondary-container aria-selected:text-on-secondary-container',
                hidden: 'invisible',
                ...classNames,
            }}
            components={{
                Chevron: ({ orientation }) => {
                    const Icon = orientation === 'left' ? ChevronLeft : ChevronRight;
                    return <Icon className="h-4 w-4" />;
                },
            }}
            {...props}
        />
    );
}
Calendar.displayName = 'Calendar';

export { Calendar };
