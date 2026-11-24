import { Link, usePage } from '@inertiajs/react';

export default function CentralLayout({ title, actions, children }) {
    const { auth, flash } = usePage().props;

    return (
        <div className="min-h-screen bg-slate-50">
            <nav className="border-b border-slate-200 bg-white">
                <div className="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
                    <div className="flex items-center gap-8">
                        <Link
                            href={route('central.tenants.index')}
                            className="text-lg font-bold text-slate-900"
                        >
                            Blogs <span className="text-indigo-600">Admin</span>
                        </Link>
                        <Link
                            href={route('central.tenants.index')}
                            className={
                                'text-sm font-medium ' +
                                (route().current('central.tenants.*')
                                    ? 'text-indigo-600'
                                    : 'text-slate-600 hover:text-slate-900')
                            }
                        >
                            Tenants
                        </Link>
                    </div>

                    <div className="flex items-center gap-4 text-sm">
                        <span className="text-slate-600">{auth.user.name}</span>
                        <Link
                            href={route('central.logout')}
                            method="post"
                            as="button"
                            className="font-medium text-slate-600 hover:text-slate-900"
                        >
                            Log out
                        </Link>
                    </div>
                </div>
            </nav>

            <main className="mx-auto max-w-6xl px-4 py-8 sm:px-6">
                <div className="mb-6 flex items-center justify-between gap-4">
                    <h1 className="text-2xl font-bold text-slate-900">
                        {title}
                    </h1>
                    {actions}
                </div>

                {flash?.success && (
                    <div className="mb-6 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-800 ring-1 ring-green-200">
                        {flash.success}
                    </div>
                )}

                {children}
            </main>
        </div>
    );
}
