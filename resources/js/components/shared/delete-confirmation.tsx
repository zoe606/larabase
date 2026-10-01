import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { cn } from '@/lib/utils';
import { AlertTriangle, Loader2 } from 'lucide-react';
import { ReactNode } from 'react';

interface DeleteConfirmationBaseProps {
    title?: string;
    description?: string;
    onConfirm: () => void;
    confirmText?: string;
    cancelText?: string;
    variant?: 'default' | 'destructive';
}

interface DeleteConfirmationTriggerProps extends DeleteConfirmationBaseProps {
    children: ReactNode;
    processing?: boolean;
    isOpen?: never;
    onClose?: never;
    isDeleting?: never;
}

interface DeleteConfirmationControlledProps extends DeleteConfirmationBaseProps {
    isOpen: boolean;
    onClose: () => void;
    isDeleting?: boolean;
    children?: never;
    processing?: never;
}

type DeleteConfirmationProps = DeleteConfirmationTriggerProps | DeleteConfirmationControlledProps;

export function DeleteConfirmation(props: DeleteConfirmationProps) {
    const {
        title = 'Are you sure?',
        description = 'This action cannot be undone. This will permanently delete the item.',
        onConfirm,
        confirmText = 'Delete',
        cancelText = 'Cancel',
        variant = 'destructive',
    } = props;

    // Controlled mode (isOpen/onClose)
    if ('isOpen' in props && props.isOpen !== undefined) {
        const { isOpen, onClose, isDeleting = false } = props;

        return (
            <AlertDialog open={isOpen} onOpenChange={(open) => !open && onClose()}>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <div className="flex items-start gap-4">
                            {variant === 'destructive' && (
                                <div className="bg-destructive/10 flex size-10 shrink-0 items-center justify-center rounded-full">
                                    <AlertTriangle className="text-destructive size-5" />
                                </div>
                            )}
                            <div className="space-y-2">
                                <AlertDialogTitle>{title}</AlertDialogTitle>
                                <AlertDialogDescription>{description}</AlertDialogDescription>
                            </div>
                        </div>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel disabled={isDeleting}>{cancelText}</AlertDialogCancel>
                        <AlertDialogAction
                            onClick={onConfirm}
                            disabled={isDeleting}
                            className={cn(
                                variant === 'destructive' &&
                                    'bg-destructive text-destructive-foreground hover:bg-destructive/90 focus:ring-destructive',
                            )}
                        >
                            {isDeleting && <Loader2 className="mr-2 size-4 animate-spin" />}
                            {confirmText}
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        );
    }

    // Trigger mode (children as trigger)
    const { children, processing = false } = props as DeleteConfirmationTriggerProps;

    return (
        <AlertDialog>
            <AlertDialogTrigger asChild>{children}</AlertDialogTrigger>
            <AlertDialogContent>
                <AlertDialogHeader>
                    <div className="flex items-start gap-4">
                        {variant === 'destructive' && (
                            <div className="bg-destructive/10 flex size-10 shrink-0 items-center justify-center rounded-full">
                                <AlertTriangle className="text-destructive size-5" />
                            </div>
                        )}
                        <div className="space-y-2">
                            <AlertDialogTitle>{title}</AlertDialogTitle>
                            <AlertDialogDescription>{description}</AlertDialogDescription>
                        </div>
                    </div>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel disabled={processing}>{cancelText}</AlertDialogCancel>
                    <AlertDialogAction
                        onClick={onConfirm}
                        disabled={processing}
                        className={cn(
                            variant === 'destructive' && 'bg-destructive text-destructive-foreground hover:bg-destructive/90 focus:ring-destructive',
                        )}
                    >
                        {processing && <Loader2 className="mr-2 size-4 animate-spin" />}
                        {confirmText}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    );
}
