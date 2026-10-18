import { ThemePicker } from '@/components/shared/theme-picker';
import { initializeTheme } from '@/hooks/use-appearance';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

vi.mock('@inertiajs/react', () => ({ usePage: () => ({ props: { setting: { warna: '#006a60' } } }) }));

beforeEach(() => {
    localStorage.clear();
    vi.stubGlobal('matchMedia', vi.fn().mockReturnValue({ matches: false, addEventListener: vi.fn(), removeEventListener: vi.fn() }));
    initializeTheme('#006a60');
});

afterEach(() => {
    document.getElementById('md3-theme')?.remove();
    vi.unstubAllGlobals();
});

describe('ThemePicker', () => {
    it('lists App default plus the four seed presets', () => {
        render(<ThemePicker />);
        for (const name of ['App default', 'Teal', 'Blue', 'Green', 'Orange']) {
            expect(screen.getByRole('radio', { name })).toBeInTheDocument();
        }
        expect(screen.getByRole('radio', { name: 'App default' })).toBeChecked();
    });

    it('selects a preset and persists it', async () => {
        render(<ThemePicker />);
        await userEvent.click(screen.getByRole('radio', { name: 'Blue' }));
        expect(screen.getByRole('radio', { name: 'Blue' })).toBeChecked();
        expect(localStorage.getItem('colorSeed')).toBe('#0061a4');
    });

    it('is one tab stop and moves the choice with the arrow keys', async () => {
        render(
            <>
                <button type="button">Before</button>
                <ThemePicker />
            </>,
        );
        await userEvent.click(screen.getByRole('button', { name: 'Before' }));
        await userEvent.tab();
        expect(screen.getByRole('radio', { name: 'App default' })).toHaveFocus();

        // Hold the key like a person does: the radio group moves focus after a tick
        // and only selects the newly focused option while an arrow key is down.
        await userEvent.keyboard('{ArrowRight>}');
        await waitFor(() => expect(screen.getByRole('radio', { name: 'Teal' })).toHaveFocus());
        await userEvent.keyboard('{/ArrowRight}');
        expect(screen.getByRole('radio', { name: 'Teal' })).toBeChecked();
        expect(localStorage.getItem('colorSeed')).toBe('#006a60');

        await userEvent.tab();
        expect(document.activeElement?.getAttribute('role')).not.toBe('radio');
    });
});
