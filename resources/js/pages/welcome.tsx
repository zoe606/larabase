import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link, usePage } from '@inertiajs/react';

const features = [
    ['Authentication', 'Login, registration, profiles, and email verification.'],
    ['Access control', 'Users, roles, permissions, and configurable menus.'],
    ['Operations', 'Files, audit logs, backups, queues, and monitoring.'],
    ['API foundation', 'Sanctum authentication, standard responses, and OpenAPI documentation.'],
    ['Development', 'Actions, Queries, CRUD generators, TypeScript, and automated tests.'],
    ['Deployment', 'Docker configuration and documented installation steps.'],
];

export default function Welcome() {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="Larabase" />
            <main className="bg-surface text-on-surface mx-auto flex min-h-screen max-w-5xl flex-col gap-8 px-4 py-16 sm:px-6">
                <div className="flex flex-col gap-4">
                    <h1 className="text-display-md">Larabase</h1>
                    <p className="text-body-lg text-on-surface-variant">A production-ready Laravel and React starter for your application.</p>
                    <div className="flex flex-wrap gap-3">
                        {auth.user ? (
                            <Button asChild>
                                <Link href="/dashboard">Open dashboard</Link>
                            </Button>
                        ) : (
                            <>
                                <Button asChild>
                                    <Link href="/login">Log in</Link>
                                </Button>
                                <Button asChild variant="outline">
                                    <Link href="/register">Register</Link>
                                </Button>
                            </>
                        )}
                    </div>
                </div>
                <div className="grid gap-4 md:grid-cols-2">
                    {features.map(([title, description]) => (
                        <Card key={title}>
                            <CardHeader>
                                <CardTitle>{title}</CardTitle>
                            </CardHeader>
                            <CardContent>{description}</CardContent>
                        </Card>
                    ))}
                </div>
            </main>
        </>
    );
}
