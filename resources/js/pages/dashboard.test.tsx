import { render, screen } from '@testing-library/react';
import { type PropsWithChildren } from 'react';
import { describe, expect, it, vi } from 'vitest';
import Dashboard from './dashboard';

vi.mock('@/layouts/app-layout', () => ({
    default: ({ children }: PropsWithChildren) => <main>{children}</main>,
}));

vi.mock('@inertiajs/react', () => ({ Head: () => null }));

describe('platform dashboard', () => {
    it('renders platform labels and the supplied counts', () => {
        render(<Dashboard platform={{ users: 12, roles: 3, permissions: 28, audit_events: 41 }} />);

        expect(screen.getByRole('heading', { name: 'Platform overview' })).toBeInTheDocument();
        for (const label of ['Users', 'Roles', 'Permissions', 'Audit events']) {
            expect(screen.getByText(label)).toBeInTheDocument();
        }
        for (const count of ['12', '3', '28', '41']) {
            expect(screen.getByText(count)).toBeInTheDocument();
        }
    });
});
