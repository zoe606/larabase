import { DeleteConfirmation, EmptyState, PageHeader } from '@/components/shared';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { getInitials } from '@/lib/initials';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/react';
import dayjs from 'dayjs';
import 'dayjs/locale/id';
import relativeTime from 'dayjs/plugin/relativeTime';
import { Users } from 'lucide-react';

dayjs.extend(relativeTime);
dayjs.locale('id');

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'User Management',
        href: '/users',
    },
];

interface User {
    id: number;
    name: string;
    email: string;
    created_at: string;
    roles: {
        id: number;
        name: string;
    }[];
}

interface Props {
    users: {
        data: User[];
        current_page: number;
        last_page: number;
        links: { url: string | null; label: string; active: boolean }[];
    };
}

export default function UserIndex({ users }: Props) {
    const { delete: destroy, processing } = useForm({});

    const handleDelete = (id: number) => {
        destroy(`/users/${id}`, {
            preserveScroll: true,
        });
    };

    const handleResetPassword = (id: number) => {
        router.put(`/users/${id}/reset-password`, {}, { preserveScroll: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="User Management" />
            <div className="space-y-6 p-4 md:p-6">
                <PageHeader
                    title="User Management"
                    description="Manage user data and their roles within the system."
                    action={
                        <Link href="/users/create">
                            <Button className="w-full md:w-auto" size="sm">
                                + Add User
                            </Button>
                        </Link>
                    }
                />

                <div className="bg-background space-y-2 divide-y rounded-md border">
                    {users.data.length === 0 ? (
                        <EmptyState
                            icon={Users}
                            title="No users found"
                            description="No user data available yet."
                            action={
                                <Link href="/users/create">
                                    <Button variant="outline" size="sm">
                                        + Add User
                                    </Button>
                                </Link>
                            }
                        />
                    ) : (
                        users.data.map((user) => (
                            <div
                                key={user.id}
                                className="hover:bg-muted/50 flex flex-col justify-between gap-4 px-4 py-5 transition md:flex-row md:items-center"
                            >
                                {/* Avatar and Information */}
                                <div className="flex flex-1 items-start gap-4">
                                    <div className="bg-muted text-primary flex h-12 w-12 items-center justify-center rounded-full text-lg font-semibold">
                                        {getInitials(user.name)}
                                    </div>
                                    <div className="space-y-1">
                                        <div className="text-base font-medium">{user.name}</div>
                                        <div className="text-muted-foreground text-sm">{user.email}</div>
                                        <div className="text-muted-foreground text-xs italic">Registered {dayjs(user.created_at).fromNow()}</div>
                                        {user.roles.length > 0 && (
                                            <div className="mt-2 flex flex-wrap gap-1">
                                                {user.roles.map((role) => (
                                                    <Badge key={role.id} variant="secondary" className="text-xs font-normal">
                                                        {role.name}
                                                    </Badge>
                                                ))}
                                            </div>
                                        )}
                                    </div>
                                </div>

                                {/* Actions */}
                                <div className="flex flex-wrap gap-2 md:justify-end">
                                    <Link href={`/users/${user.id}/edit`}>
                                        <Button size="sm" variant="outline">
                                            Edit
                                        </Button>
                                    </Link>

                                    <AlertDialog>
                                        <AlertDialogTrigger asChild>
                                            <Button size="sm" variant="secondary">
                                                Reset
                                            </Button>
                                        </AlertDialogTrigger>
                                        <AlertDialogContent>
                                            <AlertDialogHeader>
                                                <AlertDialogTitle>Reset Password?</AlertDialogTitle>
                                                <AlertDialogDescription>
                                                    Password for <strong>{user.name}</strong> will be reset to:
                                                    <br />
                                                    <code className="bg-muted rounded px-2 py-1 text-sm">ResetPasswordNya</code>
                                                </AlertDialogDescription>
                                            </AlertDialogHeader>
                                            <AlertDialogFooter>
                                                <AlertDialogCancel>Cancel</AlertDialogCancel>
                                                <AlertDialogAction onClick={() => handleResetPassword(user.id)} disabled={processing}>
                                                    Yes, Reset
                                                </AlertDialogAction>
                                            </AlertDialogFooter>
                                        </AlertDialogContent>
                                    </AlertDialog>

                                    <DeleteConfirmation
                                        title="Delete User?"
                                        description={`User ${user.name} will be permanently deleted.`}
                                        onConfirm={() => handleDelete(user.id)}
                                        processing={processing}
                                        confirmText="Yes, Delete"
                                    >
                                        <Button size="sm" variant="destructive">
                                            Delete
                                        </Button>
                                    </DeleteConfirmation>
                                </div>
                            </div>
                        ))
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
