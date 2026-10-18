import { AppTopBar } from '@/components/app/top-app-bar';
import { SidebarProvider } from '@/components/ui/sidebar';
import { act, render, screen } from '@testing-library/react';
import type { ReactNode } from 'react';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

vi.mock('@inertiajs/react', () => ({
    usePage: () => ({ props: { auth: { user: { id: 1, name: 'Ada Admin', email: 'a@a.test', avatar: '' } } } }),
    Link: ({ href, children, ...rest }: { href: string; children: ReactNode }) => (
        <a href={href} {...rest}>
            {children}
        </a>
    ),
}));
vi.mock('@/components/shared/notification-badge', () => ({ NotificationBadge: () => null }));
vi.mock('@/components/shared/appearance-select', () => ({ AppearanceDropdown: () => null }));
vi.mock('@/components/shared/user-menu-content', () => ({ UserMenuContent: () => null }));

beforeEach(() => {
    vi.stubGlobal('matchMedia', vi.fn().mockReturnValue({ matches: false, addEventListener: vi.fn(), removeEventListener: vi.fn() }));
});

afterEach(() => {
    vi.unstubAllGlobals();
    Object.defineProperty(window, 'scrollY', { configurable: true, value: 0 });
});

function renderBar(breadcrumbs: { title: string; href: string }[]) {
    return render(
        <SidebarProvider>
            <AppTopBar breadcrumbs={breadcrumbs} />
        </SidebarProvider>,
    );
}

describe('AppTopBar', () => {
    it('shows the page title and a drawer button on top-level pages', () => {
        renderBar([{ title: 'Users', href: '/users' }]);
        expect(screen.getByRole('heading', { level: 1, name: 'Users' }).className).toContain('text-title-lg');
        expect(screen.getByRole('button', { name: 'Open navigation' })).toBeInTheDocument();
        expect(screen.queryByRole('link', { name: 'Back' })).toBeNull();
    });

    it('shows a back link to the parent on detail pages', () => {
        renderBar([
            { title: 'Users', href: '/users' },
            { title: 'Groceries', href: '#' },
        ]);
        expect(screen.getByRole('heading', { level: 1, name: 'Groceries' })).toBeInTheDocument();
        expect(screen.getByRole('link', { name: 'Back' })).toHaveAttribute('href', '/users');
    });

    it('keeps the drawer button on detail pages so phones can still open navigation', () => {
        renderBar([
            { title: 'Users', href: '/users' },
            { title: 'Groceries', href: '#' },
        ]);
        expect(screen.getByRole('button', { name: 'Open navigation' }).className).toContain('md:hidden');
    });

    it('never renders a breadcrumb trail', () => {
        renderBar([
            { title: 'Roles', href: '/roles' },
            { title: 'Editor', href: '/roles/5' },
            { title: 'Edit Permissions', href: '#' },
        ]);
        expect(screen.queryByText('Roles')).toBeNull();
        expect(screen.queryByText('Editor')).toBeNull();
    });

    it('switches to surface-container once the page scrolls', () => {
        renderBar([{ title: 'Users', href: '/users' }]);
        const header = screen.getByRole('banner');
        expect(header).toHaveAttribute('data-scrolled', 'false');
        act(() => {
            Object.defineProperty(window, 'scrollY', { configurable: true, value: 12 });
            window.dispatchEvent(new Event('scroll'));
        });
        expect(header).toHaveAttribute('data-scrolled', 'true');
        expect(header.className).toContain('bg-surface-container');
    });

    it('opens the account menu from the avatar', () => {
        renderBar([{ title: 'Users', href: '/users' }]);
        expect(screen.getByRole('button', { name: 'Account menu' })).toHaveTextContent('AA');
    });
});
