import { describe, expect, it } from 'vitest';

/** The top app bar renders the page title as the only h1, so pages inside AppLayout start at h2. */
const ROOT = '/resources/js/';
const sources = import.meta.glob(
    ['/resources/js/pages/**/*.tsx', '/resources/js/components/shared/page-header.tsx', '!/resources/js/**/__tests__/**'],
    {
        query: '?raw',
        import: 'default',
        eager: true,
    },
) as Record<string, string>;
const files = Object.entries(sources)
    .filter(([path, src]) => src.includes('AppLayout') || path.endsWith('page-header.tsx'))
    .map(([path]) => path.slice(ROOT.length));

describe('page headings', () => {
    it.each(files)('%s leaves the h1 to the top app bar', (file) => {
        expect(sources[ROOT + file]).not.toMatch(/<h1[\s>]/);
    });
});
