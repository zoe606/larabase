import { applySeed, normalizeSeed, resolveSeed } from '@/lib/md3-theme';
import { useEffect, useState } from 'react';

export type Appearance = 'light' | 'dark' | 'system';

const APPEARANCE_KEY = 'appearance';
const SEED_KEY = 'colorSeed';
const LEGACY_THEME_KEY = 'colorTheme';
const LEGACY_THEME_CLASSES = ['theme-zinc', 'theme-oled', 'theme-slate', 'theme-teal'];

let appSeed: string | null = null;

function read(key: string): string | null {
    try {
        return localStorage.getItem(key);
    } catch {
        return null;
    }
}

function write(key: string, value: string | null): void {
    try {
        if (value === null) localStorage.removeItem(key);
        else localStorage.setItem(key, value);
    } catch {
        // storage unavailable (private mode): the choice simply isn't persisted
    }
}

const prefersDark = () => typeof window !== 'undefined' && window.matchMedia('(prefers-color-scheme: dark)').matches;

function applyAppearance(appearance: Appearance): void {
    if (typeof document === 'undefined') return;
    const isDark = appearance === 'dark' || (appearance === 'system' && prefersDark());
    document.documentElement.classList.toggle('dark', isDark);
}

function applyCurrentSeed(): void {
    applySeed(resolveSeed({ preset: read(SEED_KEY), appSeed }));
}

export function setAppSeed(seed?: string | null): void {
    appSeed = normalizeSeed(seed);
    applyCurrentSeed();
}

export function initializeTheme(initialAppSeed?: string | null): void {
    if (typeof window === 'undefined') return;

    write(LEGACY_THEME_KEY, null);
    document.documentElement.classList.remove(...LEGACY_THEME_CLASSES);

    applyAppearance((read(APPEARANCE_KEY) as Appearance | null) ?? 'system');
    setAppSeed(initialAppSeed);

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        applyAppearance((read(APPEARANCE_KEY) as Appearance | null) ?? 'system');
    });
}

export function useAppearance() {
    const [appearance, setAppearance] = useState<Appearance>('system');
    const [colorSeed, setColorSeed] = useState<string | null>(null);

    const updateAppearance = (mode: Appearance) => {
        setAppearance(mode);
        write(APPEARANCE_KEY, mode);
        applyAppearance(mode);
    };

    const updateColorSeed = (seed: string | null) => {
        const normalized = normalizeSeed(seed);
        setColorSeed(normalized);
        write(SEED_KEY, normalized);
        applyCurrentSeed();
    };

    useEffect(() => {
        setAppearance((read(APPEARANCE_KEY) as Appearance | null) ?? 'system');
        setColorSeed(normalizeSeed(read(SEED_KEY)));
    }, []);

    return { appearance, updateAppearance, colorSeed, updateColorSeed } as const;
}
