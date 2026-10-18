import { Switch } from '@/components/ui/switch';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it } from 'vitest';

describe('Switch', () => {
    it('renders the MD3 52x32 track and toggles', async () => {
        render(<Switch aria-label="Notify" />);
        const sw = screen.getByRole('switch', { name: 'Notify' });
        expect(sw.className).toContain('h-8');
        expect(sw.className).toContain('w-[52px]');
        await userEvent.click(sw);
        expect(sw).toHaveAttribute('data-state', 'checked');
        expect(sw.querySelector('svg')).not.toBeNull();
    });

    it('omits the check icon when showIcon is false', async () => {
        render(<Switch aria-label="Notify" showIcon={false} defaultChecked />);
        expect(screen.getByRole('switch').querySelector('svg')).toBeNull();
    });
});
