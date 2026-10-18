// md3-exception-file: seed and preset colours are theme data
import {
    argbFromHex,
    customColor,
    type DynamicColor,
    Hct,
    hexFromArgb,
    MaterialDynamicColors as M,
    SchemeTonalSpot,
} from '@material/material-color-utilities';

export const DEFAULT_SEED = '#006a60';

export const SEED_PRESETS: ReadonlyArray<{ name: string; hex: string }> = [
    { name: 'Teal', hex: '#006a60' },
    { name: 'Blue', hex: '#0061a4' },
    { name: 'Green', hex: '#386a20' },
    { name: 'Orange', hex: '#8b5000' },
];

const SCHEME_ROLES: ReadonlyArray<[string, DynamicColor]> = [
    ['primary', M.primary],
    ['on-primary', M.onPrimary],
    ['primary-container', M.primaryContainer],
    ['on-primary-container', M.onPrimaryContainer],
    ['secondary', M.secondary],
    ['on-secondary', M.onSecondary],
    ['secondary-container', M.secondaryContainer],
    ['on-secondary-container', M.onSecondaryContainer],
    ['tertiary', M.tertiary],
    ['on-tertiary', M.onTertiary],
    ['tertiary-container', M.tertiaryContainer],
    ['on-tertiary-container', M.onTertiaryContainer],
    ['error', M.error],
    ['on-error', M.onError],
    ['error-container', M.errorContainer],
    ['on-error-container', M.onErrorContainer],
    ['surface', M.surface],
    ['on-surface', M.onSurface],
    ['on-surface-variant', M.onSurfaceVariant],
    ['surface-container-lowest', M.surfaceContainerLowest],
    ['surface-container-low', M.surfaceContainerLow],
    ['surface-container', M.surfaceContainer],
    ['surface-container-high', M.surfaceContainerHigh],
    ['surface-container-highest', M.surfaceContainerHighest],
    ['outline', M.outline],
    ['outline-variant', M.outlineVariant],
    ['inverse-surface', M.inverseSurface],
    ['inverse-on-surface', M.inverseOnSurface],
    ['inverse-primary', M.inversePrimary],
    ['scrim', M.scrim],
];

const CUSTOM_COLORS: ReadonlyArray<[string, string]> = [
    ['success', '#2e7d32'],
    ['warning', '#f9a825'],
    ['info', '#0277bd'],
];

export const MD3_ROLE_NAMES: readonly string[] = [
    ...SCHEME_ROLES.map(([name]) => name),
    ...CUSTOM_COLORS.flatMap(([name]) => [name, `on-${name}`, `${name}-container`, `on-${name}-container`]),
];

const HEX = /^#[0-9a-f]{6}$/i;

export function normalizeSeed(input: string | null | undefined): string | null {
    return typeof input === 'string' && HEX.test(input) ? input.toLowerCase() : null;
}

export function resolveSeed({ preset, appSeed }: { preset: string | null; appSeed: string | null | undefined }): string {
    return normalizeSeed(preset) ?? normalizeSeed(appSeed) ?? DEFAULT_SEED;
}

export function buildRoleMap(seed: string, dark: boolean): Record<string, string> {
    const source = argbFromHex(normalizeSeed(seed) ?? DEFAULT_SEED);
    const scheme = new SchemeTonalSpot(Hct.fromInt(source), dark, 0);
    const roles: Record<string, string> = {};

    for (const [name, color] of SCHEME_ROLES) {
        roles[name] = hexFromArgb(color.getArgb(scheme));
    }

    for (const [name, value] of CUSTOM_COLORS) {
        const group = customColor(source, { name, value: argbFromHex(value), blend: true })[dark ? 'dark' : 'light'];
        roles[name] = hexFromArgb(group.color);
        roles[`on-${name}`] = hexFromArgb(group.onColor);
        roles[`${name}-container`] = hexFromArgb(group.colorContainer);
        roles[`on-${name}-container`] = hexFromArgb(group.onColorContainer);
    }

    return roles;
}

function toDeclarations(roles: Record<string, string>): string {
    return MD3_ROLE_NAMES.map((name) => `--md-${name}:${roles[name]};`).join('');
}

export function buildThemeCss(seed: string): string {
    return `html:root{${toDeclarations(buildRoleMap(seed, false))}}html.dark{${toDeclarations(buildRoleMap(seed, true))}}`;
}

export function applySeed(seed: string): void {
    if (typeof document === 'undefined') return;

    let tag = document.getElementById('md3-theme') as HTMLStyleElement | null;
    if (!tag) {
        tag = document.createElement('style');
        tag.id = 'md3-theme';
    }
    tag.textContent = buildThemeCss(seed);
    document.head.appendChild(tag); // (re)appending keeps it last so it wins over Vite-injected CSS
}
