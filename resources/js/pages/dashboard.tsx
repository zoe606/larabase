import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }];

interface DashboardProps {
    platform: {
        users: number;
        roles: number;
        permissions: number;
        audit_events: number;
    };
}

export default function Dashboard({ platform }: DashboardProps) {
    const counts = [
        { label: 'Users', value: platform.users },
        { label: 'Roles', value: platform.roles },
        { label: 'Permissions', value: platform.permissions },
        { label: 'Audit events', value: platform.audit_events },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex flex-col gap-4 p-4 md:gap-6 md:p-6">
                <h2 className="text-headline-sm">Platform overview</h2>
                <div className="grid gap-4 sm:grid-cols-2 md:gap-6 lg:grid-cols-4">
                    {counts.map(({ label, value }) => (
                        <Card key={label}>
                            <CardHeader>
                                <CardTitle>{label}</CardTitle>
                            </CardHeader>
                            <CardContent className="text-display-sm">{value}</CardContent>
                        </Card>
                    ))}
                </div>
            </div>
        </AppLayout>
    );
}
