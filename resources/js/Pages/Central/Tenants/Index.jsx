import TenantAvatar from '@/Components/TenantAvatar';
import CentralLayout from '@/Layouts/CentralLayout';
import { Head } from '@inertiajs/react';

export default function Index({ tenants }) {
    return (
        <CentralLayout title="Tenants">
            <Head title="Tenants" />

            {tenants.length === 0 ? (
                <div className="rounded-xl border border-dashed border-slate-300 bg-white p-12 text-center text-slate-500">
                    No blogs yet.
                </div>
            ) : (
                <div className="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
                    <table className="min-w-full divide-y divide-slate-200 text-sm">
                        <thead className="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th className="px-4 py-3">Blog</th>
                                <th className="px-4 py-3">Domain</th>
                                <th className="px-4 py-3">Brand color</th>
                                <th className="px-4 py-3">Created</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {tenants.map((tenant) => (
                                <tr key={tenant.id}>
                                    <td className="px-4 py-3">
                                        <div className="flex items-center gap-3">
                                            <TenantAvatar
                                                name={tenant.name}
                                                logoUrl={tenant.logo_url}
                                                color={tenant.brand_color}
                                            />
                                            <span className="font-medium text-slate-900">
                                                {tenant.name}
                                            </span>
                                        </div>
                                    </td>
                                    <td className="px-4 py-3">
                                        <a
                                            href={`http://${tenant.domain}`}
                                            className="text-indigo-600 hover:underline"
                                        >
                                            {tenant.domain}
                                        </a>
                                    </td>
                                    <td className="px-4 py-3">
                                        <span className="inline-flex items-center gap-2 font-mono text-xs text-slate-600">
                                            <span
                                                className="h-4 w-4 rounded ring-1 ring-slate-200"
                                                style={{
                                                    backgroundColor:
                                                        tenant.brand_color,
                                                }}
                                            />
                                            {tenant.brand_color}
                                        </span>
                                    </td>
                                    <td className="px-4 py-3 text-slate-500">
                                        {tenant.created_at}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
        </CentralLayout>
    );
}
