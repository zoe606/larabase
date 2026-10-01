import { FormError, LoadingButton, useFieldError } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import ComboboxPermission from '@/components/ui/combobox-permission';
import IconPicker from '@/components/ui/icon-picker';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { createMenuSchema, type CreateMenuFormData } from '@/schemas';
import { type BreadcrumbItem } from '@/types';
import { zodResolver } from '@hookform/resolvers/zod';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { useForm } from 'react-hook-form';
import { toast } from 'sonner';

interface MenuFormProps {
    menu?: {
        id: number;
        title: string;
        route: string;
        icon: string;
        parent_id: number | null;
        permission_name: string | null;
    };
    parentMenus: { id: number; title: string }[];
    permissions: string[];
}

export default function MenuForm({ menu, parentMenus, permissions }: MenuFormProps) {
    const isEdit = !!menu;
    const [processing, setProcessing] = useState(false);
    const { errors: serverErrors } = usePage().props as { errors: Record<string, string> };

    const {
        register,
        handleSubmit,
        watch,
        setValue,
        formState: { errors },
    } = useForm<CreateMenuFormData>({
        resolver: zodResolver(createMenuSchema),
        defaultValues: {
            title: menu?.title || '',
            route: menu?.route || '',
            icon: menu?.icon || '',
            parent_id: menu?.parent_id ?? null,
            permission_name: menu?.permission_name || '',
        },
    });

    const selectedIcon = watch('icon');
    const selectedParentId = watch('parent_id');
    const selectedPermission = watch('permission_name');

    // Extract useFieldError calls to component level (React Rules of Hooks)
    const hasTitleError = useFieldError('title', errors.title);
    const hasRouteError = useFieldError('route', errors.route);
    const hasParentIdError = useFieldError('parent_id', errors.parent_id);

    // Show toast notification when server-side errors occur
    useEffect(() => {
        if (serverErrors && Object.keys(serverErrors).length > 0) {
            const errorMessages = Object.values(serverErrors);
            toast.error('Validation Error', {
                description: errorMessages[0],
            });
        }
    }, [serverErrors]);

    const onSubmit = (data: CreateMenuFormData) => {
        setProcessing(true);

        if (isEdit) {
            router.put(`/menus/${menu?.id}`, data, {
                onFinish: () => setProcessing(false),
            });
        } else {
            router.post('/menus', data, {
                onFinish: () => setProcessing(false),
            });
        }
    };

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Menu Management', href: '/menus' },
        { title: isEdit ? 'Edit Menu' : 'Add Menu', href: '#' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={isEdit ? 'Edit Menu' : 'Add Menu'} />
            <div className="flex-1 p-4 md:p-6">
                <Card className="mx-auto max-w-2xl lg:max-w-4xl">
                    <CardHeader className="pb-3">
                        <CardTitle className="text-2xl font-bold tracking-tight">{isEdit ? 'Edit Menu' : 'Add New Menu'}</CardTitle>
                        <p className="text-muted-foreground text-sm">{isEdit ? 'Update menu details' : 'Create a new menu for the system'}</p>
                    </CardHeader>

                    <Separator />

                    <CardContent className="pt-6">
                        <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
                            <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="title">Menu Title *</Label>
                                    <Input
                                        id="title"
                                        {...register('title')}
                                        placeholder="Example: Dashboard"
                                        className={hasTitleError ? 'border-red-500' : ''}
                                    />
                                    <FormError field="title" clientError={errors.title} />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="route">Route</Label>
                                    <Input
                                        id="route"
                                        {...register('route')}
                                        placeholder="Example: /dashboard"
                                        className={hasRouteError ? 'border-red-500' : ''}
                                    />
                                    <FormError field="route" clientError={errors.route} />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="icon">Icon (Lucide)</Label>
                                    <IconPicker value={selectedIcon || ''} onChange={(val) => setValue('icon', val)} />
                                    <FormError field="icon" clientError={errors.icon} />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="parent_id">Parent Menu</Label>
                                    <Select
                                        value={selectedParentId?.toString() ?? '_none'}
                                        onValueChange={(v) => setValue('parent_id', v === '_none' ? null : Number(v))}
                                    >
                                        <SelectTrigger id="parent_id" className={hasParentIdError ? 'border-red-500' : ''}>
                                            <SelectValue placeholder="— None —" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="_none">— None —</SelectItem>
                                            {parentMenus.map((m) => (
                                                <SelectItem key={m.id} value={m.id.toString()}>
                                                    {m.title}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <FormError field="parent_id" clientError={errors.parent_id} />
                                </div>

                                <div className="space-y-2 md:col-span-2">
                                    <Label htmlFor="permission_name">Permission</Label>
                                    <ComboboxPermission
                                        value={selectedPermission || ''}
                                        onChange={(val) => setValue('permission_name', val)}
                                        options={permissions}
                                    />
                                    <FormError field="permission_name" clientError={errors.permission_name} />
                                </div>
                            </div>

                            <Separator />

                            <div className="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-end">
                                <Link href="/menus" className="w-full sm:w-auto">
                                    <Button type="button" variant="secondary" className="w-full">
                                        Cancel
                                    </Button>
                                </Link>
                                <LoadingButton type="submit" loading={processing} className="w-full sm:w-auto">
                                    {isEdit ? 'Save Changes' : 'Create Menu'}
                                </LoadingButton>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
