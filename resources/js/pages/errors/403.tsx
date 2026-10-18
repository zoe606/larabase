import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/react';

export default function Error403() {
    return (
        <>
            <Head title="403 - Akses Ditolak" />
            <div className="flex min-h-screen flex-col items-center justify-center p-6 text-center">
                <h1 className="text-error text-4xl font-bold">403</h1>
                <p className="text-on-surface-variant mt-2">Kamu tidak punya izin untuk mengakses halaman ini.</p>
                <Link href="/dashboard">
                    <Button className="mt-6">Kembali ke Dashboard</Button>
                </Link>
            </div>
        </>
    );
}
