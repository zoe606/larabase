import { Select, SelectTrigger, SelectValue } from '@/components/ui/select';
import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';

function renderTrigger(variant?: 'field' | 'chip') {
    render(
        <Select value="all">
            <SelectTrigger variant={variant}>
                <SelectValue />
            </SelectTrigger>
        </Select>,
    );
    return screen.getByRole('combobox').className;
}

describe('SelectTrigger', () => {
    it('defaults to an outlined full-width field', () => {
        const c = renderTrigger();
        expect(c).toContain('w-full');
        expect(c).toContain('rounded-shape-md');
    });

    it('renders a compact pill chip for filter rows', () => {
        const c = renderTrigger('chip');
        expect(c).toContain('h-8');
        expect(c).toContain('rounded-shape-full');
        expect(c).not.toContain('w-full');
        expect(c).not.toContain('rounded-shape-md');
    });
});
