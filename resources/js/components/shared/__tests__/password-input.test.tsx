import { IconInput, PasswordInput } from '@/components/shared/icon-input';
import { fireEvent, render, screen } from '@testing-library/react';
import { Mail } from 'lucide-react';
import { describe, expect, it } from 'vitest';

describe('IconInput', () => {
    it('puts a decorative icon in front of the field and leaves room for it', () => {
        const { container } = render(<IconInput icon={Mail} aria-label="Email" />);
        const input = screen.getByRole('textbox', { name: 'Email' });
        expect(input.className).toContain('pl-11');
        expect(container.querySelector('svg[aria-hidden="true"]')).not.toBeNull();
    });
});

describe('PasswordInput', () => {
    it('hides the password until the visitor asks to see it', () => {
        render(<PasswordInput aria-label="Password" defaultValue="secret" />);
        const input = screen.getByLabelText('Password');
        expect(input).toHaveAttribute('type', 'password');

        fireEvent.click(screen.getByRole('button', { name: 'Show password' }));
        expect(input).toHaveAttribute('type', 'text');

        fireEvent.click(screen.getByRole('button', { name: 'Hide password' }));
        expect(input).toHaveAttribute('type', 'password');
    });

    it('keeps the toggle out of the tab order so Tab goes to the next field', () => {
        render(<PasswordInput aria-label="Password" />);
        expect(screen.getByRole('button', { name: 'Show password' })).toHaveAttribute('tabindex', '-1');
    });
});
