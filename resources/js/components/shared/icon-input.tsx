import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';
import { Eye, EyeOff, Lock, type LucideIcon } from 'lucide-react';
import * as React from 'react';

interface IconInputProps extends React.ComponentProps<'input'> {
    icon: LucideIcon;
    trailing?: React.ReactNode;
}

const IconInput = React.forwardRef<HTMLInputElement, IconInputProps>(({ icon: Icon, trailing, className, ...props }, ref) => (
    <div className="relative">
        <Icon aria-hidden className="text-on-surface-variant pointer-events-none absolute top-1/2 left-4 size-5 -translate-y-1/2" />
        <Input ref={ref} className={cn('pl-11', trailing && 'pr-12', className)} {...props} />
        {trailing && <div className="absolute top-1/2 right-1 -translate-y-1/2">{trailing}</div>}
    </div>
));
IconInput.displayName = 'IconInput';

const PasswordInput = React.forwardRef<HTMLInputElement, Omit<React.ComponentProps<'input'>, 'type'>>((props, ref) => {
    const [visible, setVisible] = React.useState(false);

    return (
        <IconInput
            ref={ref}
            icon={Lock}
            type={visible ? 'text' : 'password'}
            trailing={
                // Out of the tab order so Tab moves from the password straight to the next field.
                <Button
                    type="button"
                    variant="text"
                    size="icon"
                    tabIndex={-1}
                    aria-label={visible ? 'Hide password' : 'Show password'}
                    onClick={() => setVisible((v) => !v)}
                >
                    {visible ? <EyeOff /> : <Eye />}
                </Button>
            }
            {...props}
        />
    );
});
PasswordInput.displayName = 'PasswordInput';

export { IconInput, PasswordInput };
