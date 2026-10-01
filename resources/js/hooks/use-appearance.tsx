import { useEffect, useState } from 'react';

export type Appearance = 'light' | 'dark' | 'system';
export type ColorTheme = 'zinc' | 'oled' | 'slate';

const THEME_CLASSES = ['theme-zinc', 'theme-oled', 'theme-slate'] as const;

const prefersDark = () => typeof window !== 'undefined' && window.matchMedia('(prefers-color-scheme: dark)').matches;

const applyColorTheme = (theme: ColorTheme) => {
    if (typeof document === 'undefined') return;
    document.documentElement.classList.remove(...THEME_CLASSES);
    document.documentElement.classList.add(`theme-${theme}`);
};

const applyTheme = (appearance: Appearance) => {
    if (typeof document === 'undefined') return;
    const isDark = appearance === 'dark' || (appearance === 'system' && prefersDark());
    document.documentElement.classList.toggle('dark', isDark);

    const savedColorTheme = (localStorage.getItem('colorTheme') as ColorTheme) || 'zinc';
    applyColorTheme(savedColorTheme);
};

export function initializeTheme() {
    if (typeof window === 'undefined') return;
    const savedAppearance = (localStorage.getItem('appearance') as Appearance) || 'system';
    applyTheme(savedAppearance);

    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    mediaQuery.addEventListener('change', () => {
        const currentAppearance = localStorage.getItem('appearance') as Appearance;
        applyTheme(currentAppearance || 'system');
    });
}

export function useAppearance() {
    const [appearance, setAppearance] = useState<Appearance>('system');
    const [colorTheme, setColorTheme] = useState<ColorTheme>('zinc');

    const updateAppearance = (mode: Appearance) => {
        setAppearance(mode);
        localStorage.setItem('appearance', mode);
        applyTheme(mode);
    };

    const updateColorTheme = (theme: ColorTheme) => {
        setColorTheme(theme);
        localStorage.setItem('colorTheme', theme);
        applyColorTheme(theme);
    };

    useEffect(() => {
        const savedAppearance = localStorage.getItem('appearance') as Appearance;
        const savedColorTheme = localStorage.getItem('colorTheme') as ColorTheme;
        updateAppearance(savedAppearance || 'system');
        if (savedColorTheme) {
            setColorTheme(savedColorTheme);
        }
    }, []);

    return { appearance, updateAppearance, colorTheme, updateColorTheme };
}
