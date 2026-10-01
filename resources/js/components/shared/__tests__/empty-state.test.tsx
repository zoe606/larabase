import { EmptyState } from '@/components/shared/empty-state';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';

// Mock Inertia's Link component
vi.mock('@inertiajs/react', () => ({
    Link: ({ href, children }: { href: string; children: React.ReactNode }) => <a href={href}>{children}</a>,
}));

describe('EmptyState', () => {
    it('renders with default text when no props provided', () => {
        render(<EmptyState />);

        expect(screen.getByText('No data available')).toBeInTheDocument();
    });

    it('renders custom title and description', () => {
        render(<EmptyState title="Nothing here" description="Try adding something new" />);

        expect(screen.getByText('Nothing here')).toBeInTheDocument();
        expect(screen.getByText('Try adding something new')).toBeInTheDocument();
    });

    it('renders no-data variant defaults', () => {
        render(<EmptyState variant="no-data" />);

        expect(screen.getByText('No data yet')).toBeInTheDocument();
        expect(screen.getByText('Get started by creating your first item.')).toBeInTheDocument();
    });

    it('renders no-results variant defaults', () => {
        render(<EmptyState variant="no-results" />);

        expect(screen.getByText('No results found')).toBeInTheDocument();
        expect(screen.getByText('Try adjusting your search or filters.')).toBeInTheDocument();
    });

    it('renders error variant defaults', () => {
        render(<EmptyState variant="error" />);

        expect(screen.getByText('Something went wrong')).toBeInTheDocument();
        expect(screen.getByText('Please try again later.')).toBeInTheDocument();
    });

    it('renders no-permission variant defaults', () => {
        render(<EmptyState variant="no-permission" />);

        expect(screen.getByText('Access denied')).toBeInTheDocument();
        expect(screen.getByText("You don't have permission to view this.")).toBeInTheDocument();
    });

    it('allows overriding variant defaults with custom title and description', () => {
        render(<EmptyState variant="error" title="Oops!" description="Something unexpected happened" />);

        expect(screen.getByText('Oops!')).toBeInTheDocument();
        expect(screen.getByText('Something unexpected happened')).toBeInTheDocument();
        expect(screen.queryByText('Something went wrong')).not.toBeInTheDocument();
    });

    it('renders suggestions list', () => {
        const suggestions = ['Check your spelling', 'Try broader terms'];

        render(<EmptyState variant="no-results" suggestions={suggestions} />);

        expect(screen.getByText('Check your spelling')).toBeInTheDocument();
        expect(screen.getByText('Try broader terms')).toBeInTheDocument();
    });

    it('renders an action button with onClick handler', async () => {
        const user = userEvent.setup();
        const handleClick = vi.fn();

        render(<EmptyState title="Empty" action={{ label: 'Create New', onClick: handleClick }} />);

        const button = screen.getByRole('button', { name: 'Create New' });
        await user.click(button);

        expect(handleClick).toHaveBeenCalledOnce();
    });

    it('renders an action link with href', () => {
        render(<EmptyState title="Empty" action={{ label: 'Go to Dashboard', href: '/dashboard' }} />);

        const link = screen.getByRole('link', { name: 'Go to Dashboard' });
        expect(link).toHaveAttribute('href', '/dashboard');
    });

    it('renders custom ReactNode as action', () => {
        render(<EmptyState title="Empty" action={<span data-testid="custom-action">Custom</span>} />);

        expect(screen.getByTestId('custom-action')).toBeInTheDocument();
    });

    it('applies custom className', () => {
        const { container } = render(<EmptyState className="my-custom-class" />);

        expect(container.firstChild).toHaveClass('my-custom-class');
    });
});
