import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { format, parseISO } from 'date-fns';
import { CalendarIcon } from 'lucide-react';
import * as React from 'react';

interface DatePickerProps {
    /** The selected date value (ISO string format: YYYY-MM-DD) */
    value?: string | null;
    /** Callback when date changes (returns ISO string format: YYYY-MM-DD) */
    onChange: (date: string | null) => void;
    /** Placeholder text when no date selected */
    placeholder?: string;
    /** Additional class names for the trigger button */
    className?: string;
    /** Disable the date picker */
    disabled?: boolean;
    /** Minimum selectable date */
    minDate?: Date;
    /** Maximum selectable date */
    maxDate?: Date;
    /** Date format for display (default: 'PPP' = 'April 29th, 2024') */
    displayFormat?: string;
    /** ID for form label association */
    id?: string;
}

export function DatePicker({
    value,
    onChange,
    placeholder = 'Pick a date',
    className,
    disabled = false,
    minDate,
    maxDate,
    displayFormat = 'PPP',
    id,
}: DatePickerProps) {
    const [open, setOpen] = React.useState(false);

    // Convert ISO string to Date object for the calendar
    const selectedDate = React.useMemo(() => {
        if (!value) return undefined;
        try {
            return parseISO(value);
        } catch {
            return undefined;
        }
    }, [value]);

    // Handle date selection
    const handleSelect = (date: Date | undefined) => {
        if (date) {
            // Convert to ISO string (YYYY-MM-DD)
            const isoDate = format(date, 'yyyy-MM-dd');
            onChange(isoDate);
        } else {
            onChange(null);
        }
        setOpen(false);
    };

    // Format the display value
    const displayValue = React.useMemo(() => {
        if (!selectedDate) return null;
        try {
            return format(selectedDate, displayFormat);
        } catch {
            return null;
        }
    }, [selectedDate, displayFormat]);

    return (
        <Popover open={open} onOpenChange={setOpen}>
            <PopoverTrigger asChild>
                <Button
                    id={id}
                    variant="outline"
                    disabled={disabled}
                    className={cn('w-full justify-start text-left font-normal', !value && 'text-muted-foreground', className)}
                >
                    <CalendarIcon className="mr-2 h-4 w-4" />
                    {displayValue ?? <span>{placeholder}</span>}
                </Button>
            </PopoverTrigger>
            <PopoverContent className="w-auto p-0" align="start">
                <Calendar
                    mode="single"
                    selected={selectedDate}
                    onSelect={handleSelect}
                    disabled={(date) => {
                        if (minDate && date < minDate) return true;
                        if (maxDate && date > maxDate) return true;
                        return false;
                    }}
                    autoFocus
                />
            </PopoverContent>
        </Popover>
    );
}
