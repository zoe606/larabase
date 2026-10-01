import { FormError, LoadingButton, useFieldError } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { createUserSchema, updateUserSchema, type CreateUserFormData, type UpdateUserFormData } from '@/schemas';
import { BreadcrumbItem } from '@/types';
import { zodResolver } from '@hookform/resolvers/zod';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { useForm } from 'react-hook-form';
import { toast } from 'sonner';

interface Role {
    id: number;
    name: string;
}

interface User {
    id?: number;
    name: string;
    email: string;
    roles?: string[];
}

interface Props {
    user?: User;
    roles: Role[];
    currentRoles?: string[];
}

export default function UserForm({ user, roles, currentRoles }: Props) {
    const isEdit = !!user;
    const [processing, setProcessing] = useState(false);
    const { errors: serverErrors } = usePage().props as { errors: Record<string, string> };

    const {
        register,
        handleSubmit,
        watch,
        setValue,
        formState: { errors },
    } = useForm<CreateUserFormData | UpdateUserFormData>({
        resolver: zodResolver(isEdit ? updateUserSchema : createUserSchema),
        defaultValues: {
            name: user?.name || '',
            email: user?.email || '',
            password: '',
            password_confirmation: '',
            roles: currentRoles || [],
        },
    });

    const selectedRoles = watch('roles');

    // Extract useFieldError calls to component level (React Rules of Hooks)
    const hasNameError = useFieldError('name', errors.name);
    const hasEmailError = useFieldError('email', errors.email);
    const hasPasswordError = useFieldError('password', errors.password);
    const hasPasswordConfirmationError = useFieldError('password_confirmation', errors.password_confirmation);
    const hasRolesError = useFieldError('roles', errors.roles);

    // Show toast notification when server-side errors occur
    useEffect(() => {
        if (serverErrors && Object.keys(serverErrors).length > 0) {
            const errorMessages = Object.values(serverErrors);
            toast.error('Validation Error', {
                description: errorMessages[0], // Show first error in toast
            });
        }
    }, [serverErrors]);

    const onSubmit = (data: CreateUserFormData | UpdateUserFormData) => {
        setProcessing(true);

        if (isEdit) {
            router.put(`/users/${user?.id}`, data, {
                onFinish: () => setProcessing(false),
            });
        } else {
            router.post('/users', data, {
                onFinish: () => setProcessing(false),
            });
        }
    };

    const handleRoleChange = (roleName: string, checked: boolean) => {
        if (checked) {
            setValue('roles', [...selectedRoles, roleName]);
        } else {
            setValue(
                'roles',
                selectedRoles.filter((r) => r !== roleName),
            );
        }
    };

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'User Management', href: '/users' },
        { title: isEdit ? 'Edit User' : 'Create User', href: '#' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={isEdit ? 'Edit User' : 'Create User'} />
            <div className="flex-1 p-4 md:p-6">
                <Card className="mx-auto max-w-3xl lg:max-w-5xl">
                    <CardHeader className="pb-3">
                        <CardTitle className="text-2xl font-bold tracking-tight">{isEdit ? 'Edit User' : 'Create New User'}</CardTitle>
                        <p className="text-muted-foreground text-sm">{isEdit ? 'Update user data and roles' : 'Enter user data and set roles'}</p>
                    </CardHeader>

                    <Separator />

                    <CardContent className="pt-5">
                        <form onSubmit={handleSubmit(onSubmit)} className="space-y-8">
                            <div className="space-y-4">
                                {/* Name */}
                                <div>
                                    <Label htmlFor="name" className="mb-2 block">
                                        Name
                                    </Label>
                                    <Input id="name" placeholder="Full name" {...register('name')} className={hasNameError ? 'border-red-500' : ''} />
                                    <FormError field="name" clientError={errors.name} />
                                </div>

                                {/* Email */}
                                <div>
                                    <Label htmlFor="email" className="mb-2 block">
                                        Email
                                    </Label>
                                    <Input
                                        id="email"
                                        placeholder="Email address"
                                        {...register('email')}
                                        className={hasEmailError ? 'border-red-500' : ''}
                                    />
                                    <FormError field="email" clientError={errors.email} />
                                </div>

                                {/* Password */}
                                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div>
                                        <Label htmlFor="password" className="mb-2 block">
                                            Password {isEdit ? '(Optional)' : ''}
                                        </Label>
                                        <Input
                                            id="password"
                                            type="password"
                                            placeholder="••••••••"
                                            {...register('password')}
                                            className={hasPasswordError ? 'border-red-500' : ''}
                                        />
                                        <FormError field="password" clientError={errors.password} />
                                    </div>

                                    <div>
                                        <Label htmlFor="password_confirmation" className="mb-2 block">
                                            Confirm Password {isEdit ? '(Optional)' : ''}
                                        </Label>
                                        <Input
                                            id="password_confirmation"
                                            type="password"
                                            placeholder="••••••••"
                                            {...register('password_confirmation')}
                                            className={hasPasswordConfirmationError ? 'border-red-500' : ''}
                                        />
                                        <FormError field="password_confirmation" clientError={errors.password_confirmation} />
                                    </div>
                                </div>

                                {/* Roles */}
                                <div>
                                    <Label className="mb-3 block">Roles</Label>
                                    <div className={`space-y-3 rounded-lg border p-4 ${hasRolesError ? 'border-red-500' : ''}`}>
                                        {roles.map((role) => (
                                            <div key={role.id} className="flex items-center space-x-2">
                                                <Checkbox
                                                    id={`role-${role.id}`}
                                                    checked={selectedRoles.includes(role.name)}
                                                    onCheckedChange={(checked) => handleRoleChange(role.name, !!checked)}
                                                />
                                                <Label htmlFor={`role-${role.id}`} className="cursor-pointer text-sm font-normal">
                                                    {role.name}
                                                </Label>
                                            </div>
                                        ))}
                                    </div>
                                    <FormError field="roles" clientError={errors.roles} />
                                </div>
                            </div>

                            <Separator />

                            <div className="flex flex-col-reverse justify-end gap-3 pt-2 sm:flex-row">
                                <Link href="/users" className="w-full sm:w-auto">
                                    <Button type="button" variant="secondary" className="w-full">
                                        Back
                                    </Button>
                                </Link>
                                <LoadingButton type="submit" loading={processing} className="w-full sm:w-auto">
                                    {isEdit ? 'Save Changes' : 'Create User'}
                                </LoadingButton>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
