import { Badge } from '@/components/ui/badge';
import { Chip } from '@/components/ui/chip';
import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';

describe('Badge', () => {
    it.each([
        ['success', 'bg-success-container'],
        ['warning', 'bg-warning-container'],
        ['info', 'bg-info-container'],
        ['destructive', 'bg-error-container'],
        ['default', 'bg-secondary-container'],
    ] as const)('%s uses its MD3 container colour', (variant, expected) => {
        render(<Badge variant={variant}>x</Badge>);
        expect(screen.getByText('x').className).toContain(expected);
        expect(screen.getByText('x').className).toContain('rounded-shape-sm');
    });
});

describe('Chip', () => {
    it('renders a filter chip that reflects selection', () => {
        render(
            <Chip variant="filter" selected>
                Food
            </Chip>,
        );
        const chip = screen.getByRole('button', { name: 'Food' });
        expect(chip).toHaveAttribute('aria-pressed', 'true');
        expect(chip.className).toContain('bg-secondary-container');
    });

    it('renders an unselected assist chip with an outline', () => {
        render(<Chip>Import</Chip>);
        const chip = screen.getByRole('button', { name: 'Import' });
        expect(chip).not.toHaveAttribute('aria-pressed');
        expect(chip.className).toContain('border-outline');
    });
});
