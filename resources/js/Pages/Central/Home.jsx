import { Head, Link } from '@inertiajs/react';

export default function Home() {
    return (
        <>
            <Head title="Blogs" />
            <main className="flex min-h-screen flex-col items-center justify-center bg-gray-50 px-6 text-center">
                <h1 className="text-4xl font-bold tracking-tight text-gray-900">
                    Blogs
                </h1>
                <p className="mt-3 max-w-md text-gray-600">
                    A white-label blogging platform. Each company gets its own
                    branded blog on its own domain.
                </p>
                <Link
                    href={route('central.tenants.index')}
                    className="mt-8 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
                >
                    Admin dashboard
                </Link>
            </main>
        </>
    );
}
