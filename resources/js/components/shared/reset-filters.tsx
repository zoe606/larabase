import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { RotateCcw } from 'lucide-react';

interface ResetFiltersProps {
    /** Callback when reset is triggered - handles the actual reset logic */
    onReset: () => void;
    /** Button variant */
    variant?: 'outline' | 'ghost' | 'secondary';
    /** Button size */
    size?: 'default' | 'sm' | 'icon';
    /** Show text label */
    showLabel?: boolean;
    /** Additional CSS classes */
    className?: string;
}

export function ResetFilters({ onReset, variant = 'outline', size = 'default', showLabel = true, className }: ResetFiltersProps) {
    return (
        <Button type="button" variant={variant} size={size} onClick={onReset} className={cn('shrink-0', className)}>
            <RotateCcw className={showLabel ? 'mr-2 h-4 w-4' : 'h-4 w-4'} />
            {showLabel && 'Reset'}
        </Button>
    );
}
