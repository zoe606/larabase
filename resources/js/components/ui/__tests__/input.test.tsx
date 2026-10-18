import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';

describe('text fields', () => {
    it('renders an outlined input with 12px corners', () => {
        render(<Input aria-label="Email" />);
        const c = screen.getByRole('textbox', { name: 'Email' }).className;
        expect(c).toContain('rounded-shape-md');
        expect(c).toContain('border-outline');
        expect(c).not.toContain('rounded-shape-xs');
    });

    it('gives a textarea the same corners as an input', () => {
        render(<Textarea aria-label="Notes" />);
        expect(screen.getByRole('textbox', { name: 'Notes' }).className).toContain('rounded-shape-md');
    });
});
