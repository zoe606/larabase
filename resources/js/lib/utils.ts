import { type ClassValue, clsx } from 'clsx';
import { extendTailwindMerge } from 'tailwind-merge';

const typescale = ['display', 'headline', 'title', 'body', 'label'].flatMap((role) => ['lg', 'md', 'sm'].map((size) => `${role}-${size}`));

const twMerge = extendTailwindMerge({
    extend: {
        theme: {
            text: typescale,
            radius: ['shape-none', 'shape-xs', 'shape-sm', 'shape-md', 'shape-lg', 'shape-xl', 'shape-full'],
            shadow: ['elevation-0', 'elevation-1', 'elevation-2', 'elevation-3', 'elevation-4', 'elevation-5'],
            ease: ['emphasized', 'emphasized-decelerate', 'emphasized-accelerate', 'standard'],
        },
    },
});

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
