import { DataTablePagination } from '@/components/shared';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

interface Activity {
    id: number;
    description: string;
    created_at: string;
    causer: { name: string } | null;
    properties: Record<string, unknown>;
    subject_type: string | null;
}

interface Props {
    logs: {
        data: Activity[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: { url: string | null; label: string; active: boolean }[];
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Audit Log',
        href: '/audit-logs',
    },
];

export default function AuditLogIndex({ logs }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Audit Log" />
            <div className="flex-1 p-4 md:p-6">
                <Card>
                    <CardHeader className="pb-3">
                        <CardTitle className="text-2xl font-bold">Audit Log</CardTitle>
                        <p className="text-muted-foreground text-sm">User activity history in the system</p>
                    </CardHeader>

                    <Separator />

                    <CardContent className="space-y-4 pt-6">
                        {/* List Logs */}
                        {logs.data.length === 0 ? (
                            <p className="text-muted-foreground text-center">No activity logs.</p>
                        ) : (
                            logs.data.map((log) => (
                                <div key={log.id} className="bg-muted/50 hover:bg-muted/70 rounded-md border px-4 py-3 transition">
                                    <div className="text-foreground text-sm font-medium">{log.description}</div>
                                    <div className="text-muted-foreground text-xs">
                                        {log.causer?.name ?? 'System'} • {new Date(log.created_at).toLocaleString()}
                                        {log.subject_type ? ` • ${log.subject_type.split('\\').pop()}` : ''}
                                    </div>
                                    {log.properties && Object.keys(log.properties).length > 0 && (
                                        <pre className="bg-muted mt-2 max-h-48 overflow-auto rounded p-2 text-xs">
                                            {JSON.stringify(log.properties, null, 2)}
                                        </pre>
                                    )}
                                </div>
                            ))
                        )}

                        {/* Pagination */}
                        {logs.last_page > 1 && <DataTablePagination pagination={logs} />}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
