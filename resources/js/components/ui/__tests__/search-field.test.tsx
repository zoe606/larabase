import { SearchField } from '@/components/ui/search-field';
import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';

describe('SearchField', () => {
    it('renders a pill search bar input with a search role', () => {
        render(<SearchField placeholder="Search users" />);
        const input = screen.getByRole('searchbox');
        expect(input).toHaveAttribute('placeholder', 'Search users');
        expect(input.className).toContain('rounded-shape-full');
        expect(input.className).toContain('bg-surface-container-highest');
    });

    it('applies className to the wrapper for layout and forwards input props', () => {
        render(<SearchField className="md:flex-1" data-testid="s" disabled />);
        const input = screen.getByTestId('s');
        expect(input).toBeDisabled();
        expect(input.parentElement?.className).toContain('md:flex-1');
    });
});
