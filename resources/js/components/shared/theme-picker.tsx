import { useAppearance } from '@/hooks/use-appearance';
import { SEED_PRESETS } from '@/lib/md3-theme';
import { cn } from '@/lib/utils';
import { usePage } from '@inertiajs/react';
import * as RadioGroup from '@radix-ui/react-radio-group';
import { Check } from 'lucide-react';

// Radio values must be strings, so the app default seed (null) gets a stand-in value.
const APP_DEFAULT = 'app-default';

export function ThemePicker() {
    const { colorSeed, updateColorSeed } = useAppearance();
    const { setting } = usePage().props;
    // md3-exception: fallback seed colour
    const options = [{ name: 'App default', hex: null, swatch: setting?.warna ?? '#006a60' }, ...SEED_PRESETS.map((p) => ({ ...p, swatch: p.hex }))];

    return (
        <div className="space-y-3">
            <div>
                <p className="text-title-sm">Color theme</p>
                <p className="text-body-md text-on-surface-variant">Pick a seed colour. Light and dark palettes are generated from it.</p>
            </div>
            <RadioGroup.Root
                aria-label="Color theme"
                value={colorSeed ?? APP_DEFAULT}
                onValueChange={(value) => updateColorSeed(value === APP_DEFAULT ? null : value)}
                className="flex flex-wrap gap-3"
            >
                {options.map((option) => {
                    const selected = colorSeed === option.hex;
                    return (
                        <RadioGroup.Item
                            key={option.name}
                            value={option.hex ?? APP_DEFAULT}
                            aria-label={option.name}
                            className={cn(
                                'state-layer rounded-shape-md text-on-surface-variant focus-visible:outline-ring flex w-24 flex-col items-center gap-2 p-3 focus-visible:outline-2',
                                selected && 'bg-secondary-container text-on-secondary-container',
                            )}
                        >
                            <span className="relative grid size-10 place-items-center rounded-full" style={{ backgroundColor: option.swatch }}>
                                {/* md3-exception: the check sits on an arbitrary seed swatch, so it stays white with a shadow */}
                                {selected && <Check className="size-5 text-white drop-shadow" />}
                            </span>
                            <span className="text-label-md">{option.name}</span>
                        </RadioGroup.Item>
                    );
                })}
            </RadioGroup.Root>
        </div>
    );
}
