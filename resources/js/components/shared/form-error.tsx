import { cn } from '@/lib/utils';
import { usePage } from '@inertiajs/react';
import { AlertCircle } from 'lucide-react';
import { FieldError, Merge } from 'react-hook-form';

// Accept error types from react-hook-form (including array field errors and merged types)
// eslint-disable-next-line @typescript-eslint/no-explicit-any
type ClientErrorType = FieldError | Merge<FieldError, any> | { message?: string; root?: { message: string } } | undefined;

interface FormErrorProps {
    field: string;
    clientError?: ClientErrorType;
    className?: string;
}

function getErrorMessage(error: ClientErrorType): string | undefined {
    if (!error) return undefined;
    if ('message' in error && typeof error.message === 'string') {
        return error.message;
    }
    if ('root' in error && error.root?.message) {
        return error.root.message;
    }
    return undefined;
}

export function FormError({ field, clientError, className }: FormErrorProps) {
    const { errors } = usePage().props as { errors: Record<string, string> };

    const clientMessage = getErrorMessage(clientError);
    const serverError = errors?.[field];
    const message = clientMessage || serverError;

    if (!message) return null;

    return (
        <p className={cn('text-destructive mt-1.5 flex items-center gap-1.5 text-sm', className)} role="alert">
            <AlertCircle className="size-3.5 shrink-0" />
            <span>{message}</span>
        </p>
    );
}

export function useFieldError(field: string, clientError?: ClientErrorType): boolean {
    const { errors } = usePage().props as { errors: Record<string, string> };
    return !!(getErrorMessage(clientError) || errors?.[field]);
}
