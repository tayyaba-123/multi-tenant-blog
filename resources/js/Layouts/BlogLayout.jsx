import { Link, usePage } from '@inertiajs/react';

export default function BlogLayout({ children }) {
    const { auth, tenant } = usePage().props;

    return (
        <div className="flex min-h-screen flex-col bg-white text-gray-900">
            <header className="border-b border-gray-100">
                <div className="mx-auto flex h-16 max-w-3xl items-center justify-between px-6">
                    <Link
                        href={route('home')}
                        className="text-lg font-bold tracking-tight"
                    >
                        {tenant.name}
                    </Link>

                    <Link
                        href={auth.user ? route('dashboard') : route('login')}
                        className="text-sm font-medium text-gray-600 hover:text-gray-900"
                    >
                        {auth.user ? 'Dashboard' : 'Writer login'}
                    </Link>
                </div>
            </header>

            <main className="mx-auto w-full max-w-3xl flex-1 px-6 py-12">
                {children}
            </main>

            <footer className="border-t border-gray-100 py-8 text-center text-sm text-gray-500">
                © {new Date().getFullYear()} {tenant.name}
            </footer>
        </div>
    );
}
