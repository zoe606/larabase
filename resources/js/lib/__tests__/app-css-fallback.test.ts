import { buildRoleMap, DEFAULT_SEED, MD3_ROLE_NAMES } from '@/lib/md3-theme';
import { describe, expect, it } from 'vitest';
import css from '../../../css/app.css?raw';

function block(selector: string): string {
    const start = css.indexOf(`${selector} {`);
    expect(start, `${selector} block missing`).toBeGreaterThan(-1);
    return css.slice(start, css.indexOf('}', start));
}

describe('app.css static fallback', () => {
    it.each([
        [':root', false],
        ['.dark', true],
    ] as const)('%s matches the generated default scheme', (selector, dark) => {
        const roles = buildRoleMap(DEFAULT_SEED, dark);
        const body = block(selector);
        for (const name of MD3_ROLE_NAMES) {
            expect(body, `--md-${name}`).toContain(`--md-${name}: ${roles[name]};`);
        }
    });

    it('no longer ships the static colour themes', () => {
        expect(css).not.toMatch(/\.theme-(zinc|oled|slate|teal)/);
    });
});
