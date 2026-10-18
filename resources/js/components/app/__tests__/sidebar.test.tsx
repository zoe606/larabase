import { AppSidebar } from '@/components/app/sidebar';
import { SidebarProvider } from '@/components/ui/sidebar';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import type { ReactNode } from 'react';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

const menus = [
    { id: 1, title: 'Dashboard', route: '/dashboard', icon: 'LayoutGrid' },
    { id: 2, title: 'Users', route: '/users', icon: 'Wallet' },
    { id: 3, title: 'Roles', route: '/roles', icon: 'Shield' },
    {
        id: 4,
        title: 'Access',
        route: null,
        icon: 'Users',
        children: [{ id: 5, title: 'Permissions', route: '/permissions', icon: 'Tag' }],
    },
];

vi.mock('@inertiajs/react', () => ({
    usePage: () => ({ url: '/users/3', props: { menus, setting: {} } }),
    Link: ({ href, children, ...rest }: { href: string; children: ReactNode }) => (
        <a href={href} {...rest}>
            {children}
        </a>
    ),
}));

beforeEach(() => {
    vi.stubGlobal('matchMedia', vi.fn().mockReturnValue({ matches: false, addEventListener: vi.fn(), removeEventListener: vi.fn() }));
});

afterEach(() => vi.unstubAllGlobals());

function renderSidebar(defaultOpen: boolean) {
    return render(
        <SidebarProvider defaultOpen={defaultOpen}>
            <AppSidebar />
        </SidebarProvider>,
    );
}

describe('AppSidebar drawer', () => {
    it('marks the current destination with the secondary-container pill', () => {
        renderSidebar(true);
        const active = screen.getByRole('link', { name: 'Users' });
        expect(active).toHaveAttribute('aria-current', 'page');
        expect(active.className).toContain('bg-secondary-container');
        expect(active.className).toContain('h-14');
        expect(screen.getByRole('link', { name: 'Roles' })).not.toHaveAttribute('aria-current');
    });

    it('uses title-sm group headings instead of uppercase micro-labels', () => {
        renderSidebar(true);
        const heading = screen.getByText('System');
        expect(heading.className).toContain('text-title-sm');
        expect(heading.className).not.toContain('uppercase');
    });

    it('tells assistive tech the drawer is expanded', () => {
        renderSidebar(true);
        expect(screen.getByRole('button', { name: 'Collapse navigation' })).toHaveAttribute('aria-expanded', 'true');
    });

    it('has no border on the drawer edge', () => {
        const { container } = renderSidebar(true);
        expect(container.querySelector('.border-r')).toBeNull();
    });
});

describe('AppSidebar rail', () => {
    it('keeps a caption under each icon and moves the active pill onto the icon', () => {
        renderSidebar(false);
        const active = screen.getByRole('link', { name: 'Users' });
        expect(active).toHaveAttribute('aria-current', 'page');
        expect(active.className).not.toContain('bg-secondary-container');
        const pill = active.querySelector('[data-slot="rail-indicator"]');
        expect(pill?.className).toContain('bg-secondary-container');
        expect(pill?.className).toContain('w-14');
        expect(active.className).toContain('text-label-md');
        expect(screen.getByText('Users')).toBeVisible();
    });

    it('wraps long captions onto two lines instead of cutting them off', () => {
        renderSidebar(false);
        const caption = screen.getByText('Users');
        expect(caption.className).toContain('line-clamp-2');
        expect(caption.className).not.toContain('truncate');
    });

    it('tells assistive tech the rail is collapsed', () => {
        renderSidebar(false);
        expect(screen.getByRole('button', { name: 'Expand navigation' })).toHaveAttribute('aria-expanded', 'false');
        expect(screen.getByRole('button', { name: 'Access' })).toHaveAttribute('aria-expanded', 'false');
    });

    it('hides group headings', () => {
        renderSidebar(false);
        expect(screen.queryByText('System')).toBeNull();
    });

    it('expands the drawer when a parent with children is clicked', async () => {
        renderSidebar(false);
        await userEvent.click(screen.getByRole('button', { name: 'Access' }));
        expect(await screen.findByText('System')).toBeInTheDocument();
        expect(screen.getByRole('link', { name: 'Permissions' })).toBeInTheDocument();
    });
});
