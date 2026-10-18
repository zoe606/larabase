import { usePage } from '@inertiajs/react';
import AppLogoIcon from './logo-icon';

export default function AppLogo() {
    const { setting } = usePage().props;

    const appName = setting?.nama_app || 'Larabase';
    const logo = setting?.logo || '';

    return (
        <div className="flex items-center gap-2">
            {logo ? (
                <img src={`/storage/${logo}`} alt="Logo" className="rounded-shape-sm size-8 object-contain" />
            ) : (
                <div className="rounded-shape-sm bg-primary text-on-primary flex aspect-square size-8 items-center justify-center">
                    <AppLogoIcon className="size-[1.375rem] fill-current" />
                </div>
            )}
            <span className="text-title-md text-on-surface truncate">{appName}</span>
        </div>
    );
}
