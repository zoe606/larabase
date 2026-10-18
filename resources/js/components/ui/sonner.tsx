import { Toaster as Sonner, type ToasterProps } from 'sonner';

const Toaster = (props: ToasterProps) => (
    <Sonner
        position="bottom-center"
        className="toaster group"
        toastOptions={{
            classNames: {
                toast: 'group toast !rounded-shape-xs !border-0 !bg-inverse-surface !text-inverse-on-surface !shadow-elevation-3 !text-body-md',
                description: '!text-inverse-on-surface/80',
                actionButton: '!bg-transparent !text-inverse-primary !text-label-lg',
                cancelButton: '!bg-transparent !text-inverse-on-surface/80',
                closeButton: '!bg-inverse-surface !text-inverse-on-surface !border-0',
            },
        }}
        {...props}
    />
);

export { Toaster };
