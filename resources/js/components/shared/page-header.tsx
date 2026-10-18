import { ReactNode } from 'react';

interface PageHeaderProps {
    title: string;
    description?: string;
    action?: ReactNode;
}

export function PageHeader({ title, description, action }: PageHeaderProps) {
    return (
        <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
            <div>
                <h2 className="text-2xl font-bold tracking-tight">{title}</h2>
                {description && <p className="text-on-surface-variant">{description}</p>}
            </div>
            {action && <div className="w-full md:w-auto">{action}</div>}
        </div>
    );
}
