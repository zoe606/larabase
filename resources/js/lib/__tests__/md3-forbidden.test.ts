import { findViolations } from '@/lib/md3-forbidden';
import { describe, expect, it } from 'vitest';

describe('findViolations', () => {
    it('accepts MD3 tokens', () => {
        expect(findViolations(`<span className="bg-success-container text-on-success-container" />`)).toEqual([]);
    });

    it.each([
        ['palette with a variant', `'dark:bg-gray-800'`, 'Tailwind palette colour'],
        ['side border palette', `'border-l-4 border-l-green-500'`, 'Tailwind palette colour'],
        ['white', `'text-white'`, 'Tailwind palette colour'],
        ['hex', `stroke="#3b82f6"`, 'hex colour'],
        ['short hex', `fill="#fff"`, 'hex colour'],
        ['hex with alpha', `color: '#00000080'`, 'hex colour'],
        ['pixel size', `'text-[11px]'`, 'pixel font size'],
        ['uppercase', `'uppercase tracking-wide'`, 'uppercase micro-label'],
        ['legacy token', `'text-muted-foreground'`, 'legacy muted token'],
    ])('flags %s', (_name, src, rule) => {
        expect(findViolations(src).map((v) => v.rule)).toContain(rule);
    });

    it('does not mistake an in-page link for a hex colour', () => {
        expect(findViolations(`href="#faq" href="#add-item" href="#features"`)).toEqual([]);
    });

    it('reports 1-based line numbers', () => {
        expect(findViolations(`ok\n'bg-red-500'`)[0]).toEqual({ line: 2, rule: 'Tailwind palette colour', match: 'bg-red-500' });
    });

    it('skips a line marked as an exception, or the line after the marker', () => {
        expect(findViolations(`'uppercase' // md3-exception: plate numbers`)).toEqual([]);
        expect(findViolations(`// md3-exception: seed colour\nconst seed = '#006a60';`)).toEqual([]);
        expect(findViolations(`// md3-exception: seed colour\nconst a = 1;\nconst seed = '#006a60';`)).toHaveLength(1);
    });

    it('skips a whole file marked as an exception', () => {
        expect(findViolations(`// md3-exception-file: theme seeds are data\nconst a = '#006a60';\nconst b = '#0061a4';`)).toEqual([]);
    });
});
