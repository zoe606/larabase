import { cn } from '@/lib/utils';
import { AlertCircle } from 'lucide-react';
import { HTMLAttributes } from 'react';

export default function InputError({ message, className = '', ...props }: HTMLAttributes<HTMLParagraphElement> & { message?: string }) {
    return message ? (
        <p {...props} className={cn('text-destructive mt-1.5 flex items-center gap-1.5 text-sm', className)} role="alert">
            <AlertCircle className="size-3.5 shrink-0" />
            <span>{message}</span>
        </p>
    ) : null;
}
