import { findViolations } from '@/lib/md3-forbidden';
import { describe, expect, it } from 'vitest';

/** Every file under resources/js/ must use MD3 tokens. Exceptions are marked inline with md3-exception. */
const ROOT = '/resources/js/';
const sources = import.meta.glob(['/resources/js/**/*.{ts,tsx}', '!/resources/js/**/__tests__/**', '!/resources/js/**/*.d.ts'], {
    query: '?raw',
    import: 'default',
    eager: true,
}) as Record<string, string>;
const files = Object.keys(sources).map((path) => path.slice(ROOT.length));

describe('MD3 tokens', () => {
    it.each(files)('%s uses only MD3 tokens', (file) => {
        expect(findViolations(sources[ROOT + file] ?? '')).toEqual([]);
    });
});
