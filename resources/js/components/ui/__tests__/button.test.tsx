import { Button } from '@/components/ui/button';
import { Fab } from '@/components/ui/fab';
import { render, screen } from '@testing-library/react';
import { Plus } from 'lucide-react';
import { describe, expect, it } from 'vitest';

const cls = (name: string) => screen.getByRole('button', { name }).className;

describe('Button', () => {
    it('defaults to a filled pill', () => {
        render(<Button>Save</Button>);
        expect(cls('Save')).toContain('bg-primary');
        expect(cls('Save')).toContain('text-on-primary');
        expect(cls('Save')).toContain('rounded-shape-full');
        expect(cls('Save')).toContain('state-layer');
    });

    it.each([
        ['default', 'bg-primary'],
        ['secondary', 'bg-secondary-container'],
        ['outline', 'border-outline'],
        ['ghost', 'text-primary'],
        ['destructive', 'bg-error'],
    ] as const)('maps legacy variant %s to its MD3 equivalent', (variant, expected) => {
        render(<Button variant={variant}>Go</Button>);
        expect(cls('Go')).toContain(expected);
    });

    it('renders the field variant like an outlined text field, for pickers and comboboxes', () => {
        render(<Button variant="field">Pick a date</Button>);
        const c = cls('Pick a date');
        expect(c).toContain('rounded-shape-md');
        expect(c).toContain('border-outline');
        expect(c).toContain('h-[var(--md-field-h)]');
        expect(c).not.toContain('rounded-shape-full');
        expect(c).not.toContain('text-primary');
    });

    it('renders ghost icon buttons in on-surface-variant, not primary', () => {
        render(
            <Button variant="ghost" size="icon" aria-label="Menu">
                <Plus />
            </Button>,
        );
        expect(cls('Menu')).toContain('text-on-surface-variant');
        expect(cls('Menu')).not.toMatch(/(^| )text-primary( |$)/);
        expect(cls('Menu')).toContain('size-10');
    });

    it('keeps caller classes last', () => {
        render(<Button className="w-full">Wide</Button>);
        expect(cls('Wide')).toMatch(/w-full$/);
    });
});

describe('Fab', () => {
    it('renders an icon FAB with an accessible label', () => {
        render(<Fab icon={Plus} aria-label="Add user" />);
        expect(cls('Add user')).toContain('bg-primary-container');
        expect(cls('Add user')).toContain('size-14');
    });

    it('renders an extended FAB when a label is given', () => {
        render(<Fab icon={Plus} label="Add user" />);
        expect(screen.getByRole('button', { name: 'Add user' })).toHaveTextContent('Add user');
        expect(cls('Add user')).toContain('h-14');
    });
});
