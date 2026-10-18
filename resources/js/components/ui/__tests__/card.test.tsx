import { Card, CardTitle } from '@/components/ui/card';
import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';

describe('Card', () => {
    it('defaults to an elevated MD3 card', () => {
        render(<Card data-testid="c" />);
        const c = screen.getByTestId('c').className;
        expect(c).toContain('rounded-shape-xl');
        expect(c).toContain('bg-card');
        expect(c).toContain('shadow-elevation-1');
        expect(c).not.toContain('border');
    });

    it.each([
        ['filled', 'bg-surface-container-highest'],
        ['outlined', 'border-outline-variant'],
    ] as const)('supports the %s variant', (variant, expected) => {
        render(<Card data-testid="c" variant={variant} />);
        expect(screen.getByTestId('c').className).toContain(expected);
    });

    it('lets callers override the shape', () => {
        render(<Card data-testid="c" className="rounded-xl" />);
        expect(screen.getByTestId('c').className).toContain('rounded-xl');
        expect(screen.getByTestId('c').className).not.toContain('rounded-shape-xl');
    });

    it('uses the title-lg typescale for titles', () => {
        render(<CardTitle>Users</CardTitle>);
        expect(screen.getByText('Users').className).toContain('text-title-lg');
    });
});
