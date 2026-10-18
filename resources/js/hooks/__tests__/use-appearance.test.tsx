import { initializeTheme, setAppSeed, useAppearance } from '@/hooks/use-appearance';
import { buildThemeCss } from '@/lib/md3-theme';
import { act, renderHook } from '@testing-library/react';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

const themeCss = () => document.getElementById('md3-theme')?.textContent;

beforeEach(() => {
    localStorage.clear();
    vi.stubGlobal('matchMedia', vi.fn().mockReturnValue({ matches: false, addEventListener: vi.fn(), removeEventListener: vi.fn() }));
});

afterEach(() => {
    document.getElementById('md3-theme')?.remove();
    document.documentElement.className = '';
    vi.unstubAllGlobals();
});

describe('initializeTheme', () => {
    it('applies the app seed when the viewer has no preset', () => {
        initializeTheme('#386a20');
        expect(themeCss()).toBe(buildThemeCss('#386a20'));
    });

    it('applies the viewer preset over the app seed', () => {
        localStorage.setItem('colorSeed', '#0061a4');
        initializeTheme('#386a20');
        expect(themeCss()).toBe(buildThemeCss('#0061a4'));
    });

    it('falls back to the default seed for an invalid app seed', () => {
        initializeTheme('not-a-colour');
        expect(themeCss()).toBe(buildThemeCss('#006a60'));
    });

    it('drops the legacy colorTheme key and theme-* classes', () => {
        localStorage.setItem('colorTheme', 'zinc');
        document.documentElement.classList.add('theme-zinc');
        initializeTheme(null);
        expect(localStorage.getItem('colorTheme')).toBeNull();
        expect(document.documentElement.classList.contains('theme-zinc')).toBe(false);
    });
});

describe('setAppSeed', () => {
    it('re-applies when the app seed changes and no preset is set', () => {
        initializeTheme('#006a60');
        setAppSeed('#8b5000');
        expect(themeCss()).toBe(buildThemeCss('#8b5000'));
    });
});

describe('useAppearance', () => {
    it('stores and applies a chosen preset, and clears it back to the app default', () => {
        initializeTheme('#386a20');
        const { result } = renderHook(() => useAppearance());

        act(() => result.current.updateColorSeed('#0061a4'));
        expect(localStorage.getItem('colorSeed')).toBe('#0061a4');
        expect(result.current.colorSeed).toBe('#0061a4');
        expect(themeCss()).toBe(buildThemeCss('#0061a4'));

        act(() => result.current.updateColorSeed(null));
        expect(localStorage.getItem('colorSeed')).toBeNull();
        expect(result.current.colorSeed).toBeNull();
        expect(themeCss()).toBe(buildThemeCss('#386a20'));
    });

    it('toggles the dark class', () => {
        const { result } = renderHook(() => useAppearance());
        act(() => result.current.updateAppearance('dark'));
        expect(document.documentElement.classList.contains('dark')).toBe(true);
        act(() => result.current.updateAppearance('light'));
        expect(document.documentElement.classList.contains('dark')).toBe(false);
    });
});
