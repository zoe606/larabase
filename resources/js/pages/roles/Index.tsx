import { DeleteConfirmation, PageHeader } from '@/components/shared';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ShieldCheck } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Role Management',
        href: '/roles',
    },
];

interface Permission {
    id: number;
    name: string;
    group: string;
}

interface Role {
    id: number;
    name: string;
    permissions: Permission[];
}

interface Props {
    roles: Role[];
    groupedPermissions: Record<string, Permission[]>;
}

export default function RoleIndex({ roles }: Props) {
    const { delete: destroy, processing } = useForm({});

    const handleDelete = (id: number) => {
        destroy(`/roles/${id}`);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Role Management" />
            <div className="flex-1 space-y-6 p-4 md:p-6">
                <PageHeader
                    title="Role Management"
                    description="Manage roles and permissions for the system"
                    action={
                        <Link href="/roles/create">
                            <Button className="w-full md:w-auto" size="sm">
                                + Add Role
                            </Button>
                        </Link>
                    }
                />

                <div className="space-y-4">
                    {roles.length === 0 && (
                        <Card>
                            <CardContent className="text-muted-foreground py-6 text-center">No role data available.</CardContent>
                        </Card>
                    )}

                    {roles.map((role) => (
                        <Card key={role.id} className="border shadow-sm">
                            <CardHeader className="bg-muted/40 space-y-2 border-b md:flex-row md:items-center md:justify-between md:space-y-0">
                                <div className="space-y-1">
                                    <CardTitle className="flex items-center gap-2 text-base font-semibold">
                                        <ShieldCheck className="text-primary h-4 w-4" />
                                        {role.name}
                                    </CardTitle>
                                    <div className="text-muted-foreground text-sm">
                                        {role.permissions.length} permission
                                        {role.permissions.length > 1 ? 's' : ''}
                                    </div>
                                </div>
                                <div className="flex gap-2">
                                    <Link href={`/roles/${role.id}/edit`}>
                                        <Button size="sm" variant="outline">
                                            Edit
                                        </Button>
                                    </Link>

                                    <DeleteConfirmation
                                        title="Are you sure?"
                                        description={`Role ${role.name} will be permanently deleted.`}
                                        onConfirm={() => handleDelete(role.id)}
                                        processing={processing}
                                        confirmText="Yes, Delete"
                                    >
                                        <Button size="sm" variant="destructive">
                                            Delete
                                        </Button>
                                    </DeleteConfirmation>
                                </div>
                            </CardHeader>

                            {role.permissions.length > 0 && (
                                <CardContent className="pt-4">
                                    <p className="text-muted-foreground mb-2 text-sm font-medium">Permissions:</p>
                                    <div className="flex flex-wrap gap-2">
                                        {role.permissions.map((permission) => (
                                            <Badge key={permission.id} variant="outline" className="border-muted text-xs font-normal">
                                                {permission.name}
                                            </Badge>
                                        ))}
                                    </div>
                                </CardContent>
                            )}
                        </Card>
                    ))}
                </div>
            </div>
        </AppLayout>
    );
}
