import AuthSplitLayout from '@/layouts/auth/auth-split-layout';
import { render, screen, within } from '@testing-library/react';
import type { ReactNode } from 'react';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

vi.mock('@inertiajs/react', () => ({
    usePage: () => ({ props: { setting: null, quote: { message: 'Save first.', author: 'Someone' } } }),
    Link: ({ href, children, ...rest }: { href: string; children: ReactNode }) => (
        <a href={href} {...rest}>
            {children}
        </a>
    ),
}));

beforeEach(() => {
    vi.stubGlobal('route', (name: string) => (name === 'home' ? '/' : `/${name}`));
    vi.stubGlobal('matchMedia', vi.fn().mockReturnValue({ matches: false, addEventListener: vi.fn(), removeEventListener: vi.fn() }));
});

afterEach(() => vi.unstubAllGlobals());

function renderLayout() {
    return render(
        <AuthSplitLayout title="Welcome back" description="Sign in to your account">
            <form aria-label="Sign in form" />
        </AuthSplitLayout>,
    );
}

describe('AuthSplitLayout', () => {
    it('puts the title and the form inside an elevated card', () => {
        renderLayout();
        const card = screen.getByRole('heading', { level: 1, name: 'Welcome back' }).closest('[data-slot="auth-card"]')!;
        expect(card).not.toBeNull();
        expect(card.className).toContain('shadow-elevation-1');
        expect(card.className).toContain('rounded-shape-xl');
        expect(within(card as HTMLElement).getByRole('form', { name: 'Sign in form' })).toBeInTheDocument();
    });

    it('keeps the card visible in dark mode with a lighter surface and an outline', () => {
        renderLayout();
        const card = screen.getByRole('heading', { level: 1, name: 'Welcome back' }).closest('[data-slot="auth-card"]')!;
        expect(card.className).toContain('dark:bg-surface-container-high');
        expect(card.className).toContain('dark:border-outline-variant');
    });

    it('draws the highlight icons on primary so they never turn black', () => {
        renderLayout();
        const [first] = within(screen.getByRole('list', { name: 'Highlights' })).getAllByRole('listitem');
        const icon = first!.querySelector('span')!;
        expect(icon.className).toContain('bg-primary');
        expect(icon.className).toContain('text-on-primary');
    });

    it('offers one light/dark toggle instead of the three-way tabs', () => {
        renderLayout();
        expect(screen.getByRole('button', { name: 'Switch to dark mode' })).toBeInTheDocument();
        expect(screen.queryByRole('button', { name: 'System' })).toBeNull();
    });

    it('shows the starter highlights and the quote', () => {
        renderLayout();
        const highlights = within(screen.getByRole('list', { name: 'Highlights' })).getAllByRole('listitem');
        expect(highlights.map((h) => h.textContent)).toEqual([
            'Authentication, roles and profiles',
            'Audit logs and file management',
            'REST API and automated tests',
        ]);
        expect(screen.getByText('Someone')).toBeInTheDocument();
    });
});
