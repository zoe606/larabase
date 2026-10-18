import { applySeed, buildRoleMap, buildThemeCss, DEFAULT_SEED, MD3_ROLE_NAMES, normalizeSeed, resolveSeed } from '@/lib/md3-theme';
import { afterEach, describe, expect, it } from 'vitest';

describe('normalizeSeed', () => {
    it.each([
        ['#006A60', '#006a60'],
        ['#0061a4', '#0061a4'],
    ])('accepts %s', (input, expected) => {
        expect(normalizeSeed(input)).toBe(expected);
    });

    it.each([null, undefined, '', 'red', '#fff', '006a60', '#00GG00', '#006a600'])('rejects %s', (input) => {
        expect(normalizeSeed(input)).toBeNull();
    });
});

describe('resolveSeed', () => {
    it('prefers a valid viewer preset', () => {
        expect(resolveSeed({ preset: '#0061a4', appSeed: '#386a20' })).toBe('#0061a4');
    });

    it('falls back to the app seed when the preset is missing or invalid', () => {
        expect(resolveSeed({ preset: null, appSeed: '#386a20' })).toBe('#386a20');
        expect(resolveSeed({ preset: 'zinc', appSeed: '#386a20' })).toBe('#386a20');
    });

    it('falls back to the default when both are missing or invalid', () => {
        expect(resolveSeed({ preset: null, appSeed: undefined })).toBe(DEFAULT_SEED);
        expect(resolveSeed({ preset: '', appSeed: '#4a31c' })).toBe(DEFAULT_SEED);
    });
});

describe('buildRoleMap', () => {
    it('generates the verified teal light roles', () => {
        const light = buildRoleMap('#006a60', false);
        expect(light.primary).toBe('#006a60');
        expect(light['on-primary']).toBe('#ffffff');
        expect(light['primary-container']).toBe('#9ef2e4');
        expect(light['secondary-container']).toBe('#cce8e2');
        expect(light.surface).toBe('#f4fbf8');
        expect(light['surface-container-lowest']).toBe('#ffffff');
        expect(light['outline-variant']).toBe('#bec9c6');
        expect(light['success-container']).toBe('#92f7bc');
        expect(light['warning-container']).toBe('#ffdf99');
        expect(light['info-container']).toBe('#c4e7ff');
    });

    it('generates distinct dark roles', () => {
        const dark = buildRoleMap('#006a60', true);
        expect(dark.primary).toBe('#82d5c8');
        expect(dark['on-primary']).toBe('#003732');
        expect(dark.surface).toBe('#0e1513');
        expect(dark['surface-container']).toBe('#1a2120');
    });

    it('produces every role for an extreme seed', () => {
        const light = buildRoleMap('#ffeb3b', false);
        const dark = buildRoleMap('#ffeb3b', true);
        for (const role of MD3_ROLE_NAMES) {
            expect(light[role], role).toMatch(/^#[0-9a-f]{6}$/);
            expect(dark[role], role).toMatch(/^#[0-9a-f]{6}$/);
        }
        expect(dark.primary).not.toBe(light.primary);
    });
});

describe('buildThemeCss', () => {
    it('emits light and dark blocks with prefixed vars', () => {
        const css = buildThemeCss('#006a60');
        expect(css).toContain('html:root{');
        expect(css).toContain('html.dark{');
        expect(css).toContain('--md-primary:#006a60;');
        expect(css).toContain('--md-primary:#82d5c8;');
    });
});

describe('applySeed', () => {
    afterEach(() => document.getElementById('md3-theme')?.remove());

    it('injects one style tag and replaces it on subsequent calls', () => {
        applySeed('#006a60');
        applySeed('#0061a4');

        const tags = document.querySelectorAll('style#md3-theme');
        expect(tags).toHaveLength(1);
        expect(tags[0]?.textContent).toBe(buildThemeCss('#0061a4'));
        expect(document.head.lastElementChild).toBe(tags[0]);
    });
});
