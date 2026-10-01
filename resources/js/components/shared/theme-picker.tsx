import { type ColorTheme, useAppearance } from '@/hooks/use-appearance';
import { cn } from '@/lib/utils';
import { Check } from 'lucide-react';

interface ThemePreset {
    value: ColorTheme;
    label: string;
    description: string;
    lightSwatches: { bg: string; card: string; accent: string };
    darkSwatches: { bg: string; card: string; accent: string };
}

const presets: ThemePreset[] = [
    {
        value: 'zinc',
        label: 'Zinc',
        description: 'Neutral gray tones',
        lightSwatches: { bg: '#ffffff', card: '#f4f4f5', accent: '#e4e4e7' },
        darkSwatches: { bg: '#0a0a0f', card: '#18181b', accent: '#27272a' },
    },
    {
        value: 'oled',
        label: 'OLED',
        description: 'High contrast',
        lightSwatches: { bg: '#ffffff', card: '#f5f5f5', accent: '#d4d4d4' },
        darkSwatches: { bg: '#000000', card: '#0d0d0d', accent: '#1a1a1a' },
    },
    {
        value: 'slate',
        label: 'Slate',
        description: 'Cool blue undertones',
        lightSwatches: { bg: '#ffffff', card: '#f1f5f9', accent: '#e2e8f0' },
        darkSwatches: { bg: '#111827', card: '#1a2234', accent: '#24334e' },
    },
];

export function ThemePicker() {
    const { colorTheme, updateColorTheme } = useAppearance();

    return (
        <div className="space-y-3">
            <div>
                <p className="text-sm font-medium">Color theme</p>
                <p className="text-muted-foreground text-sm">Choose the color palette for the interface</p>
            </div>
            <div className="grid grid-cols-3 gap-3">
                {presets.map((preset) => (
                    <button
                        key={preset.value}
                        onClick={() => updateColorTheme(preset.value)}
                        className={cn(
                            'relative flex flex-col items-center gap-2 rounded-lg border-2 p-3 transition-colors',
                            colorTheme === preset.value
                                ? 'border-primary bg-accent'
                                : 'border-transparent bg-neutral-100 hover:bg-neutral-200/80 dark:bg-neutral-800 dark:hover:bg-neutral-700/80',
                        )}
                    >
                        {colorTheme === preset.value && (
                            <div className="bg-primary text-primary-foreground absolute -top-1.5 -right-1.5 flex h-5 w-5 items-center justify-center rounded-full">
                                <Check className="h-3 w-3" />
                            </div>
                        )}
                        <div className="flex w-full flex-col gap-0.5 overflow-hidden rounded-md">
                            <div className="flex w-full gap-0.5">
                                <div className="h-5 flex-1 rounded-tl-md" style={{ backgroundColor: preset.lightSwatches.bg }} />
                                <div className="h-5 flex-1" style={{ backgroundColor: preset.lightSwatches.card }} />
                                <div className="h-5 flex-1 rounded-tr-md" style={{ backgroundColor: preset.lightSwatches.accent }} />
                            </div>
                            <div className="flex w-full gap-0.5">
                                <div className="h-5 flex-1 rounded-bl-md" style={{ backgroundColor: preset.darkSwatches.bg }} />
                                <div className="h-5 flex-1" style={{ backgroundColor: preset.darkSwatches.card }} />
                                <div className="h-5 flex-1 rounded-br-md" style={{ backgroundColor: preset.darkSwatches.accent }} />
                            </div>
                        </div>
                        <div className="text-center">
                            <p className="text-sm font-medium">{preset.label}</p>
                            <p className="text-muted-foreground text-xs">{preset.description}</p>
                        </div>
                    </button>
                ))}
            </div>
        </div>
    );
}
