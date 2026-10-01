import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';
import * as React from 'react';

interface NumberInputProps extends Omit<React.InputHTMLAttributes<HTMLInputElement>, 'onChange' | 'value'> {
    value: number | string | null | undefined;
    onChange: (value: number | null) => void;
    /** Locale for number formatting (default: 'id-ID') */
    locale?: string;
    /** Allow decimal numbers */
    allowDecimal?: boolean;
    /** Decimal places (only used when allowDecimal is true) */
    decimalPlaces?: number;
}

/**
 * Format a number with thousand separators
 */
function formatNumber(value: number | string | null | undefined, locale: string, allowDecimal: boolean): string {
    if (value === null || value === undefined || value === '') return '';

    const num = typeof value === 'string' ? parseFloat(value) : value;
    if (isNaN(num)) return '';

    if (allowDecimal) {
        return new Intl.NumberFormat(locale, {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2,
        }).format(num);
    }

    return new Intl.NumberFormat(locale, {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Math.floor(num));
}

/**
 * Parse a formatted string back to a number
 */
function parseFormattedNumber(value: string, allowDecimal: boolean): number | null {
    if (!value || value.trim() === '') return null;

    // Remove thousand separators (both . and ,) but keep decimal point
    // For Indonesian locale, thousand separator is . and decimal is ,
    // We'll handle both formats
    let cleaned = value.replace(/[^\d.,-]/g, '');

    // If there's a comma, it might be a decimal separator (European/Indonesian format)
    // Count dots and commas to determine the format
    const dots = (cleaned.match(/\./g) || []).length;
    const commas = (cleaned.match(/,/g) || []).length;

    if (commas === 1 && dots >= 0) {
        // Likely European/Indonesian format: 1.000.000,50
        cleaned = cleaned.replace(/\./g, '').replace(',', '.');
    } else if (dots === 1 && commas === 0) {
        // Likely US format: 1000000.50
        // Keep as is
    } else {
        // Multiple dots or no decimal - remove all separators
        cleaned = cleaned.replace(/[.,]/g, '');
    }

    const num = parseFloat(cleaned);
    if (isNaN(num)) return null;

    return allowDecimal ? num : Math.floor(num);
}

export function NumberInput({ value, onChange, locale = 'id-ID', allowDecimal = false, className, ...props }: NumberInputProps) {
    const [displayValue, setDisplayValue] = React.useState(() => formatNumber(value, locale, allowDecimal));

    // Update display value when external value changes
    React.useEffect(() => {
        setDisplayValue(formatNumber(value, locale, allowDecimal));
    }, [value, locale, allowDecimal]);

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const input = e.target.value;
        setDisplayValue(input);
    };

    const handleBlur = (e: React.FocusEvent<HTMLInputElement>) => {
        const parsed = parseFormattedNumber(displayValue, allowDecimal);
        onChange(parsed);
        setDisplayValue(formatNumber(parsed, locale, allowDecimal));

        // Call original onBlur if provided
        props.onBlur?.(e);
    };

    const handleKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
        // Allow: backspace, delete, tab, escape, enter, decimal point, comma
        const allowedKeys = ['Backspace', 'Delete', 'Tab', 'Escape', 'Enter', 'ArrowLeft', 'ArrowRight', 'Home', 'End'];
        if (allowedKeys.includes(e.key)) return;

        // Allow decimal point and comma for decimal numbers
        if (allowDecimal && (e.key === '.' || e.key === ',')) return;

        // Allow numbers
        if (/^\d$/.test(e.key)) return;

        // Allow Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
        if (e.ctrlKey && ['a', 'c', 'v', 'x'].includes(e.key.toLowerCase())) return;

        // Prevent all other keys
        e.preventDefault();
    };

    return (
        <Input
            {...props}
            type="text"
            inputMode={allowDecimal ? 'decimal' : 'numeric'}
            value={displayValue}
            onChange={handleChange}
            onBlur={handleBlur}
            onKeyDown={handleKeyDown}
            className={cn('text-right', className)}
        />
    );
}
