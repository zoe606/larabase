import { AppShell } from '@/components/app/shell';
import { useSidebar } from '@/components/ui/sidebar';
import { act, fireEvent, render, screen } from '@testing-library/react';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

function SidebarState() {
    const { state, toggleSidebar } = useSidebar();
    return (
        <button type="button" onClick={toggleSidebar}>
            {state}
        </button>
    );
}

function setWidth(width: number) {
    Object.defineProperty(window, 'innerWidth', { configurable: true, value: width });
}

function renderShell() {
    return render(
        <AppShell variant="sidebar">
            <SidebarState />
        </AppShell>,
    );
}

beforeEach(() => {
    vi.stubGlobal('matchMedia', vi.fn().mockReturnValue({ matches: false, addEventListener: vi.fn(), removeEventListener: vi.fn() }));
    localStorage.clear();
});

afterEach(() => {
    vi.unstubAllGlobals();
    setWidth(1024);
});

describe('AppShell', () => {
    it('opens the full drawer on desktop when no preference is stored', () => {
        setWidth(1440);
        renderShell();
        expect(screen.getByRole('button')).toHaveTextContent('expanded');
    });

    it('shows the rail on tablet widths even when the desktop preference is open', () => {
        localStorage.setItem('sidebar', 'true');
        setWidth(800);
        renderShell();
        expect(screen.getByRole('button')).toHaveTextContent('collapsed');
    });

    it('lets a tablet visitor open the drawer without changing the desktop preference', () => {
        localStorage.setItem('sidebar', 'false');
        setWidth(800);
        renderShell();
        fireEvent.click(screen.getByRole('button'));
        expect(screen.getByRole('button')).toHaveTextContent('expanded');
        expect(localStorage.getItem('sidebar')).toBe('false');
    });

    it('returns to the stored preference when the window grows to desktop', () => {
        localStorage.setItem('sidebar', 'true');
        setWidth(800);
        renderShell();
        expect(screen.getByRole('button')).toHaveTextContent('collapsed');
        act(() => {
            setWidth(1280);
            window.dispatchEvent(new Event('resize'));
        });
        expect(screen.getByRole('button')).toHaveTextContent('expanded');
    });
});
