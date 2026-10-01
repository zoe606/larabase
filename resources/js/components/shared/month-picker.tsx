import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { format, parse } from 'date-fns';
import { CalendarIcon, ChevronLeft, ChevronRight } from 'lucide-react';
import * as React from 'react';

interface MonthPickerProps {
    /** The selected month value (YYYY-MM format) */
    value?: string | null;
    /** Callback when month changes (returns YYYY-MM format) */
    onChange: (month: string) => void;
    /** Placeholder text when no month selected */
    placeholder?: string;
    /** Additional class names for the trigger button */
    className?: string;
    /** Disable the month picker */
    disabled?: boolean;
    /** ID for form label association */
    id?: string;
}

const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

export function MonthPicker({ value, onChange, placeholder = 'Pick a month', className, disabled = false, id }: MonthPickerProps) {
    const [open, setOpen] = React.useState(false);

    // Parse the YYYY-MM value to get year and month
    const selectedDate = React.useMemo(() => {
        if (!value) return null;
        try {
            return parse(value, 'yyyy-MM', new Date());
        } catch {
            return null;
        }
    }, [value]);

    const [viewYear, setViewYear] = React.useState(() => {
        return selectedDate?.getFullYear() ?? new Date().getFullYear();
    });

    // Sync viewYear when popover opens
    React.useEffect(() => {
        if (open && selectedDate) {
            setViewYear(selectedDate.getFullYear());
        }
    }, [open, selectedDate]);

    const handleSelect = (monthIndex: number) => {
        const date = new Date(viewYear, monthIndex, 1);
        onChange(format(date, 'yyyy-MM'));
        setOpen(false);
    };

    const displayValue = React.useMemo(() => {
        if (!selectedDate) return null;
        return format(selectedDate, 'MMMM yyyy');
    }, [selectedDate]);

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
            <PopoverContent className="w-auto p-3" align="start">
                {/* Year navigation */}
                <div className="mb-3 flex items-center justify-between">
                    <Button variant="ghost" size="icon" aria-label="Previous year" onClick={() => setViewYear((y) => y - 1)}>
                        <ChevronLeft className="h-4 w-4" />
                    </Button>
                    <span className="text-sm font-medium">{viewYear}</span>
                    <Button variant="ghost" size="icon" aria-label="Next year" onClick={() => setViewYear((y) => y + 1)}>
                        <ChevronRight className="h-4 w-4" />
                    </Button>
                </div>

                {/* Month grid */}
                <div className="grid grid-cols-3 gap-2">
                    {MONTHS.map((month, index) => {
                        const isSelected = selectedDate?.getFullYear() === viewYear && selectedDate?.getMonth() === index;
                        const isCurrentMonth = new Date().getFullYear() === viewYear && new Date().getMonth() === index;

                        return (
                            <Button
                                key={month}
                                variant={isSelected ? 'default' : 'ghost'}
                                size="sm"
                                className={cn('h-9 w-full', isCurrentMonth && !isSelected && 'bg-accent text-accent-foreground')}
                                onClick={() => handleSelect(index)}
                            >
                                {month}
                            </Button>
                        );
                    })}
                </div>
            </PopoverContent>
        </Popover>
    );
}
