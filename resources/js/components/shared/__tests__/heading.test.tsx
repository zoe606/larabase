import Heading from '@/components/shared/heading';
import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';

describe('Heading', () => {
    it('renders the title', () => {
        render(<Heading title="Dashboard" />);

        expect(screen.getByRole('heading', { level: 2 })).toHaveTextContent('Dashboard');
    });

    it('renders the description when provided', () => {
        render(<Heading title="Settings" description="Manage your account settings" />);

        expect(screen.getByRole('heading', { level: 2 })).toHaveTextContent('Settings');
        expect(screen.getByText('Manage your account settings')).toBeInTheDocument();
    });

    it('does not render a description when not provided', () => {
        const { container } = render(<Heading title="Users" />);

        expect(screen.getByRole('heading', { level: 2 })).toHaveTextContent('Users');
        expect(container.querySelector('p')).not.toBeInTheDocument();
    });
});
