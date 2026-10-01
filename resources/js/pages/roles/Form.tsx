import { FormError, LoadingButton, useFieldError } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { createRoleSchema, type CreateRoleFormData } from '@/schemas';
import { BreadcrumbItem } from '@/types';
import { zodResolver } from '@hookform/resolvers/zod';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { useForm } from 'react-hook-form';
import { toast } from 'sonner';

interface Permission {
    id: number;
    name: string;
    group: string;
}

interface Role {
    id?: number;
    name: string;
    permissions?: Permission[];
}

interface Props {
    role?: Role;
    groupedPermissions: Record<string, Permission[]>;
}

export default function RoleForm({ role, groupedPermissions }: Props) {
    const isEdit = !!role;
    const [processing, setProcessing] = useState(false);
    const { errors: serverErrors } = usePage().props as { errors: Record<string, string> };

    const {
        register,
        handleSubmit,
        watch,
        setValue,
        formState: { errors },
    } = useForm<CreateRoleFormData>({
        resolver: zodResolver(createRoleSchema),
        defaultValues: {
            name: role?.name || '',
            permissions: role?.permissions?.map((p) => p.name) || [],
        },
    });

    const selectedPermissions = watch('permissions') || [];

    // Show toast notification when server-side errors occur
    useEffect(() => {
        if (serverErrors && Object.keys(serverErrors).length > 0) {
            const errorMessages = Object.values(serverErrors);
            toast.error('Validation Error', {
                description: errorMessages[0],
            });
        }
    }, [serverErrors]);

    const onSubmit = (data: CreateRoleFormData) => {
        setProcessing(true);

        if (isEdit) {
            router.put(`/roles/${role?.id}`, data, {
                onFinish: () => setProcessing(false),
            });
        } else {
            router.post('/roles', data, {
                onFinish: () => setProcessing(false),
            });
        }
    };

    const togglePermission = (perm: string) => {
        if (selectedPermissions.includes(perm)) {
            setValue(
                'permissions',
                selectedPermissions.filter((p) => p !== perm),
            );
        } else {
            setValue('permissions', [...selectedPermissions, perm]);
        }
    };

    const toggleGroup = (group: string, perms: Permission[]) => {
        const allChecked = perms.every((perm) => selectedPermissions.includes(perm.name));
        if (allChecked) {
            setValue(
                'permissions',
                selectedPermissions.filter((p) => !perms.map((perm) => perm.name).includes(p)),
            );
        } else {
            const newPermissions = [...selectedPermissions];
            perms.forEach((perm) => {
                if (!newPermissions.includes(perm.name)) {
                    newPermissions.push(perm.name);
                }
            });
            setValue('permissions', newPermissions);
        }
    };

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Role Management', href: '/roles' },
        { title: isEdit ? 'Edit Role' : 'Create Role', href: '#' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={isEdit ? 'Edit Role' : 'Create Role'} />
            <div className="flex-1 p-4 md:p-6">
                <Card className="mx-auto max-w-4xl lg:max-w-6xl">
                    <CardHeader className="pb-3">
                        <CardTitle className="text-2xl font-bold tracking-tight">{isEdit ? 'Edit Role' : 'Create New Role'}</CardTitle>
                        <p className="text-muted-foreground text-sm">
                            {isEdit ? 'Update role details and permissions' : 'Create a new role and set permissions'}
                        </p>
                    </CardHeader>

                    <Separator />

                    <CardContent className="pt-5">
                        <form onSubmit={handleSubmit(onSubmit)} className="space-y-8">
                            <div className="space-y-4">
                                <div>
                                    <Label htmlFor="name" className="mb-2 block">
                                        Role Name
                                    </Label>
                                    <Input
                                        id="name"
                                        placeholder="Enter role name"
                                        {...register('name')}
                                        className={useFieldError('name', errors.name) ? 'border-red-500' : ''}
                                    />
                                    <FormError field="name" clientError={errors.name} />
                                </div>

                                <Separator />

                                <div className="space-y-6">
                                    <div>
                                        <h2 className="text-lg font-semibold">Permissions</h2>
                                        <p className="text-muted-foreground text-sm">Select permissions to grant to this role</p>
                                    </div>

                                    <div className="space-y-4">
                                        {Object.entries(groupedPermissions).map(([group, perms]) => {
                                            const allChecked = perms.every((perm) => selectedPermissions.includes(perm.name));

                                            return (
                                                <div key={group} className="bg-muted/20 rounded-lg border p-4">
                                                    <div className="mb-3 flex items-center gap-3">
                                                        <Checkbox
                                                            id={`group-${group}`}
                                                            checked={allChecked}
                                                            onCheckedChange={() => toggleGroup(group, perms)}
                                                        />
                                                        <label
                                                            htmlFor={`group-${group}`}
                                                            className="text-muted-foreground cursor-pointer text-sm font-medium tracking-wider uppercase"
                                                        >
                                                            {group}
                                                        </label>
                                                    </div>
                                                    <div className="grid grid-cols-1 gap-3 pl-7 sm:grid-cols-2 md:grid-cols-3">
                                                        {perms.map((perm) => (
                                                            <div key={perm.id} className="flex items-center gap-3">
                                                                <Checkbox
                                                                    id={`perm-${perm.id}`}
                                                                    checked={selectedPermissions.includes(perm.name)}
                                                                    onCheckedChange={() => togglePermission(perm.name)}
                                                                />
                                                                <label htmlFor={`perm-${perm.id}`} className="cursor-pointer text-sm">
                                                                    {perm.name}
                                                                </label>
                                                            </div>
                                                        ))}
                                                    </div>
                                                </div>
                                            );
                                        })}
                                    </div>
                                </div>
                            </div>

                            <Separator />

                            <div className="flex flex-col-reverse justify-end gap-3 pt-2 sm:flex-row">
                                <Link href="/roles" className="w-full sm:w-auto">
                                    <Button type="button" variant="secondary" className="w-full">
                                        Cancel
                                    </Button>
                                </Link>
                                <LoadingButton type="submit" loading={processing} className="w-full sm:w-auto">
                                    {isEdit ? 'Save Changes' : 'Create Role'}
                                </LoadingButton>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
