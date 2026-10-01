import { DeleteConfirmation, LoadingButton } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { toast } from 'sonner';

interface Backup {
    name: string;
    size: number;
    last_modified: number;
    download_url: string;
}

interface Props {
    backups: Backup[];
}

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Backup', href: '/backup' }];

function formatSize(bytes: number) {
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    if (bytes === 0) return '0 Byte';
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return `${(bytes / Math.pow(1024, i)).toFixed(2)} ${sizes[i]}`;
}

export default function BackupIndex({ backups }: Props) {
    const [isCreating, setIsCreating] = useState(false);

    const handleBackup = () => {
        setIsCreating(true);
        router.post(
            '/backup/run',
            {},
            {
                onSuccess: () => toast.success('Backup created successfully'),
                onError: () => toast.error('Failed to create backup'),
                onFinish: () => setIsCreating(false),
                preserveScroll: true,
            },
        );
    };

    const handleDelete = (filename: string) => {
        router.delete(`/backup/delete/${filename}`, {
            onSuccess: () => toast.success('Backup deleted successfully'),
            onError: () => toast.error('Failed to delete backup'),
            preserveScroll: true,
        });
    };

    return (
        <AppLayout title="Backup" breadcrumbs={breadcrumbs}>
            <Head title="Backup" />

            <div className="space-y-4 p-4 md:p-6">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between">
                        <div>
                            <CardTitle className="text-2xl font-bold">Database Backups</CardTitle>
                            <p className="text-muted-foreground text-sm">Manage system backup files</p>
                        </div>
                        <LoadingButton onClick={handleBackup} loading={isCreating}>
                            Create Backup
                        </LoadingButton>
                    </CardHeader>

                    <Separator />

                    <CardContent className="space-y-4 pt-4">
                        {backups.length === 0 ? (
                            <p className="text-muted-foreground text-center">No backups available.</p>
                        ) : (
                            <ul className="space-y-2">
                                {backups.map((backup, index) => (
                                    <li key={index} className="bg-muted/50 flex items-center justify-between rounded border p-3">
                                        <div>
                                            <div className="font-medium">{backup.name}</div>
                                            <div className="text-muted-foreground text-xs">
                                                {formatSize(backup.size)} • {new Date(backup.last_modified * 1000).toLocaleString()}
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <a href={backup.download_url} target="_blank" rel="noopener noreferrer">
                                                <Button variant="outline" size="sm">
                                                    Download
                                                </Button>
                                            </a>

                                            <DeleteConfirmation
                                                title="Delete this backup?"
                                                description={`Backup "${backup.name}" will be permanently deleted.`}
                                                onConfirm={() => handleDelete(backup.name)}
                                            >
                                                <Button variant="destructive" size="sm">
                                                    Delete
                                                </Button>
                                            </DeleteConfirmation>
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
