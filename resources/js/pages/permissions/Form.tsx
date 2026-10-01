import { FormError, LoadingButton, useFieldError } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { createPermissionSchema, type CreatePermissionFormData } from '@/schemas';
import { BreadcrumbItem } from '@/types';
import { zodResolver } from '@hookform/resolvers/zod';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';
import { useEffect, useState } from 'react';
import { useForm } from 'react-hook-form';
import { toast } from 'sonner';

interface PermissionFormProps {
    permission?: {
        id: number;
        name: string;
        group: string | null;
    };
    groups?: string[];
}

export default function PermissionForm({ permission, groups = [] }: PermissionFormProps) {
    const isEdit = !!permission;
    const [processing, setProcessing] = useState(false);
    const [newGroup, setNewGroup] = useState('');
    const { errors: serverErrors } = usePage().props as { errors: Record<string, string> };

    const {
        register,
        handleSubmit,
        watch,
        setValue,
        formState: { errors },
    } = useForm<CreatePermissionFormData>({
        resolver: zodResolver(createPermissionSchema),
        defaultValues: {
            name: permission?.name || '',
            group: permission?.group || '',
        },
    });

    const selectedGroup = watch('group');

    // Show toast notification when server-side errors occur
    useEffect(() => {
        if (serverErrors && Object.keys(serverErrors).length > 0) {
            const errorMessages = Object.values(serverErrors);
            toast.error('Validation Error', {
                description: errorMessages[0],
            });
        }
    }, [serverErrors]);

    const onSubmit = (data: CreatePermissionFormData) => {
        setProcessing(true);

        const payload = {
            name: data.name,
            group: newGroup.trim() !== '' ? newGroup.trim() : data.group,
        };

        if (isEdit) {
            router.put(`/permissions/${permission?.id}`, payload, {
                onFinish: () => setProcessing(false),
            });
        } else {
            router.post('/permissions', payload, {
                onFinish: () => setProcessing(false),
            });
        }
    };

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Permission Management', href: '/permissions' },
        { title: isEdit ? 'Edit Permission' : 'Add Permission', href: '#' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={isEdit ? 'Edit Permission' : 'Add Permission'} />

            <div className="mx-auto max-w-xl flex-1 p-4 md:p-6 lg:max-w-2xl">
                <Card>
                    <CardHeader>
                        <CardTitle className="text-2xl font-bold">{isEdit ? 'Edit Permission' : 'Add Permission'}</CardTitle>
                        <p className="text-muted-foreground text-sm">{isEdit ? 'Edit permission details' : 'Create a new permission'}</p>
                    </CardHeader>

                    <Separator />

                    <CardContent className="pt-6">
                        <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
                            {/* Permission Name */}
                            <div className="space-y-2">
                                <Label htmlFor="name">Permission Name</Label>
                                <Input
                                    id="name"
                                    placeholder="example: manage-users"
                                    {...register('name')}
                                    className={useFieldError('name', errors.name) ? 'border-red-500' : ''}
                                />
                                <FormError field="name" clientError={errors.name} />
                            </div>

                            {/* Select Group */}
                            <div className="space-y-2">
                                <Label htmlFor="group">Select Group</Label>
                                <Select value={selectedGroup || ''} onValueChange={(val) => setValue('group', val)}>
                                    <SelectTrigger className={useFieldError('group', errors.group) ? 'border-red-500' : ''}>
                                        <SelectValue placeholder="Select group..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {groups.map((group) => (
                                            <SelectItem key={group} value={group}>
                                                {group}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <FormError field="group" clientError={errors.group} />
                            </div>

                            {/* New Group */}
                            <div className="space-y-2">
                                <Label htmlFor="newGroup">Or type a new group</Label>
                                <Input
                                    id="newGroup"
                                    placeholder="example: Tender / Article / User"
                                    value={newGroup}
                                    onChange={(e) => setNewGroup(e.target.value)}
                                />
                            </div>

                            <Separator />

                            {/* Action Buttons */}
                            <div className="flex items-center justify-between pt-2">
                                <Link href="/permissions">
                                    <Button type="button" variant="secondary">
                                        <ArrowLeft className="mr-2 h-4 w-4" />
                                        Back
                                    </Button>
                                </Link>
                                <LoadingButton type="submit" loading={processing}>
                                    <Save className="mr-2 h-4 w-4" />
                                    {isEdit ? 'Save Changes' : 'Add'}
                                </LoadingButton>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
