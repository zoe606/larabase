import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { useAppearance, type Appearance } from '@/hooks/use-appearance';
import { cn } from '@/lib/utils';
import { Monitor, Moon, Sun } from 'lucide-react';
import { JSX, useState } from 'react';

export function AppearanceDropdown() {
    const { appearance, updateAppearance } = useAppearance();
    const [open, setOpen] = useState(false);

    const items: {
        value: Appearance;
        label: string;
        icon: JSX.Element;
    }[] = [
        {
            value: 'light',
            label: 'Light',
            icon: <Sun />,
        },
        {
            value: 'dark',
            label: 'Dark',
            icon: <Moon />,
        },
        {
            value: 'system',
            label: 'System',
            icon: <Monitor />,
        },
    ];

    const current = items.find((item) => item.value === appearance);

    return (
        <DropdownMenu open={open} onOpenChange={setOpen}>
            <Tooltip>
                <TooltipTrigger asChild>
                    <DropdownMenuTrigger asChild>
                        <Button variant="text" size="icon" className={cn(open && 'bg-on-surface/8')} aria-label="Change Theme">
                            {current?.icon || <Monitor />}
                        </Button>
                    </DropdownMenuTrigger>
                </TooltipTrigger>
                <TooltipContent side="bottom">Change Theme</TooltipContent>
            </Tooltip>

            <DropdownMenuContent align="end" className="w-40">
                {items.map((item) => (
                    <DropdownMenuItem
                        key={item.value}
                        onClick={() => updateAppearance(item.value)}
                        className={cn(appearance === item.value && 'text-primary [&_svg]:text-primary font-medium')}
                    >
                        {item.icon}
                        <span>{item.label}</span>
                    </DropdownMenuItem>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
