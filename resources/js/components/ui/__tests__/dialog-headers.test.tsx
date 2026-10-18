import { AlertDialogFooter, AlertDialogHeader } from '@/components/ui/alert-dialog';
import { DialogFooter, DialogHeader } from '@/components/ui/dialog';
import { SheetHeader } from '@/components/ui/sheet';
import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';

describe('dialog headers', () => {
    it.each([
        ['AlertDialogHeader', AlertDialogHeader],
        ['DialogHeader', DialogHeader],
        ['SheetHeader', SheetHeader],
    ] as const)('%s is left-aligned on every screen size', (_, Header) => {
        render(<Header data-testid="header">Title</Header>);
        const c = screen.getByTestId('header').className;
        expect(c).toContain('text-left');
        expect(c).not.toContain('text-center');
    });
});

describe('dialog footers', () => {
    it.each([
        ['AlertDialogFooter', AlertDialogFooter],
        ['DialogFooter', DialogFooter],
    ] as const)('%s wraps its actions instead of overflowing a narrow screen', (_, Footer) => {
        render(<Footer data-testid="footer">Actions</Footer>);
        const c = screen.getByTestId('footer').className;
        expect(c).toContain('flex-wrap');
        expect(c).toContain('justify-end');
    });
});
