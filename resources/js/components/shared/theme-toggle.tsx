import { Button } from '@/components/ui/button';
import { useAppearance } from '@/hooks/use-appearance';
import { Moon, Sun } from 'lucide-react';
import { useEffect, useState } from 'react';

export default function ThemeToggle() {
    const { updateAppearance } = useAppearance();
    const [isDark, setIsDark] = useState(false);

    // Read the rendered theme after mount: the stored value can be "system", and SSR has no document.
    useEffect(() => {
        setIsDark(document.documentElement.classList.contains('dark'));
    }, []);

    const toggle = () => {
        const next = !isDark;
        updateAppearance(next ? 'dark' : 'light');
        setIsDark(next);
    };

    return (
        <Button variant="text" size="icon" onClick={toggle} aria-label={isDark ? 'Switch to light mode' : 'Switch to dark mode'}>
            {isDark ? <Sun /> : <Moon />}
        </Button>
    );
}
