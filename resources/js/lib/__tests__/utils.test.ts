import { cn } from '@/lib/utils';
import { describe, expect, it } from 'vitest';

describe('cn with MD3 tokens', () => {
    it('keeps text colour alongside a typescale token', () => {
        expect(cn('text-primary', 'text-label-lg')).toBe('text-primary text-label-lg');
        expect(cn('text-on-surface text-body-md')).toBe('text-on-surface text-body-md');
    });

    it('lets a later typescale token override an earlier one', () => {
        expect(cn('text-label-lg', 'text-title-sm')).toBe('text-title-sm');
    });

    it('merges shape tokens with the radius group', () => {
        expect(cn('rounded-md', 'rounded-shape-xs')).toBe('rounded-shape-xs');
    });

    it('merges elevation tokens with the shadow group', () => {
        expect(cn('shadow-sm', 'shadow-elevation-2')).toBe('shadow-elevation-2');
    });

    it('treats MD3 role colours as colours', () => {
        expect(cn('bg-primary', 'bg-secondary-container')).toBe('bg-secondary-container');
    });
});
