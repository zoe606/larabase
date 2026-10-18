import ConfirmPassword from '@/pages/auth/confirm-password';
import ForgotPassword from '@/pages/auth/forgot-password';
import Login from '@/pages/auth/login';
import Register from '@/pages/auth/register';
import ResetPassword from '@/pages/auth/reset-password';
import { render, screen } from '@testing-library/react';
import type { ReactNode } from 'react';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

vi.mock('@inertiajs/react', async () => {
    const { useState } = await import('react');
    return {
        usePage: () => ({ props: { setting: null } }),
        Head: () => null,
        Link: ({ href, children, ...rest }: { href: string; children: ReactNode }) => (
            <a href={href} {...rest}>
                {children}
            </a>
        ),
        useForm: <T extends object>(initial: T) => {
            const [data, setDataState] = useState(initial);
            return {
                data,
                setData: (key: keyof T, value: unknown) => setDataState((d) => ({ ...d, [key]: value })),
                post: vi.fn(),
                processing: false,
                errors: {},
                reset: vi.fn(),
            };
        },
    };
});

beforeEach(() => {
    vi.stubGlobal('route', (name: string) => `/${name}`);
    vi.stubGlobal(
        'ResizeObserver',
        class {
            observe() {}
            unobserve() {}
            disconnect() {}
        },
    );
    vi.stubGlobal('matchMedia', vi.fn().mockReturnValue({ matches: false, addEventListener: vi.fn(), removeEventListener: vi.fn() }));
});

afterEach(() => vi.unstubAllGlobals());

const hasLeadingIcon = (label: string) => expect(screen.getByLabelText(label).className).toContain('pl-11');

describe('auth pages', () => {
    it('login has an email field with an icon and a password with a show toggle', () => {
        render(<Login canResetPassword />);
        hasLeadingIcon('Email address');
        hasLeadingIcon('Password');
        expect(screen.getAllByRole('button', { name: 'Show password' })).toHaveLength(1);
    });

    it('register gives every field an icon and both passwords a show toggle', () => {
        render(<Register />);
        for (const label of ['Name', 'Email address', 'Password', 'Confirm password']) hasLeadingIcon(label);
        expect(screen.getAllByRole('button', { name: 'Show password' })).toHaveLength(2);
    });

    it('reset, forgot and confirm password use the same fields', () => {
        const { unmount } = render(<ResetPassword token="t" email="a@b.test" />);
        hasLeadingIcon('Email');
        expect(screen.getAllByRole('button', { name: 'Show password' })).toHaveLength(2);
        unmount();

        const forgot = render(<ForgotPassword />);
        hasLeadingIcon('Email address');
        forgot.unmount();

        render(<ConfirmPassword />);
        hasLeadingIcon('Password');
    });
});
