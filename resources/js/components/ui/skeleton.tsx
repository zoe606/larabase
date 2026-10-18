import { cn } from '@/lib/utils';

function Skeleton({ className, ...props }: React.HTMLAttributes<HTMLDivElement>) {
    return <div className={cn('animate-pulse rounded-shape-xs bg-surface-container-highest', className)} {...props} />;
}

export { Skeleton };
