import { Button, ButtonProps } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { LoaderCircle } from 'lucide-react';
import { forwardRef } from 'react';

interface LoadingButtonProps extends ButtonProps {
    loading?: boolean;
}

const LoadingButton = forwardRef<HTMLButtonElement, LoadingButtonProps>(({ loading = false, disabled, children, className, ...props }, ref) => {
    return (
        <Button ref={ref} disabled={loading || disabled} className={cn('relative', className)} {...props}>
            {loading && <LoaderCircle className="mr-2 h-4 w-4 animate-spin" />}
            {children}
        </Button>
    );
});

LoadingButton.displayName = 'LoadingButton';

export { LoadingButton };
