import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

/**
 * Format a number with thousand separators (Indonesian locale)
 */
export function formatNumber(value: number | null | undefined, locale = 'id-ID'): string {
    if (value === null || value === undefined) return '0';
    return new Intl.NumberFormat(locale).format(value);
}
