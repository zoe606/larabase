import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';

function Example({ variant }: { variant?: 'primary' | 'segmented' }) {
    return (
        <Tabs defaultValue="a">
            <TabsList variant={variant}>
                <TabsTrigger value="a">Month</TabsTrigger>
                <TabsTrigger value="b">Year</TabsTrigger>
            </TabsList>
            <TabsContent value="a">A</TabsContent>
        </Tabs>
    );
}

describe('Tabs', () => {
    it('defaults to MD3 primary tabs with an underline indicator', () => {
        render(<Example />);
        const active = screen.getByRole('tab', { name: 'Month' });
        expect(active.className).toContain('after:bg-primary');
        expect(active.className).toContain('data-[state=active]:text-primary');
    });

    it('renders a segmented button when asked', () => {
        render(<Example variant="segmented" />);
        expect(screen.getByRole('tablist').className).toContain('rounded-shape-full');
        expect(screen.getByRole('tab', { name: 'Month' }).className).toContain('data-[state=active]:bg-secondary-container');
    });
});
