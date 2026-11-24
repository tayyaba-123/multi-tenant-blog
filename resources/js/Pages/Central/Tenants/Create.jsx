import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TenantAvatar from '@/Components/TenantAvatar';
import TextInput from '@/Components/TextInput';
import CentralLayout from '@/Layouts/CentralLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';

function Section({ title, description, children }) {
    return (
        <section className="grid gap-6 border-b border-slate-200 py-6 last:border-b-0 md:grid-cols-3">
            <div>
                <h2 className="font-semibold text-slate-900">{title}</h2>
                <p className="mt-1 text-sm text-slate-500">{description}</p>
            </div>
            <div className="space-y-4 md:col-span-2">{children}</div>
        </section>
    );
}

export default function Create({ baseDomain }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        subdomain: '',
        brand_color: '#4f46e5',
        logo: null,
        owner_name: '',
        owner_email: '',
        owner_password: '',
    });

    const [logoPreview, setLogoPreview] = useState(null);

    useEffect(() => {
        if (!data.logo) {
            setLogoPreview(null);
            return;
        }

        const url = URL.createObjectURL(data.logo);
        setLogoPreview(url);

        return () => URL.revokeObjectURL(url);
    }, [data.logo]);

    const submit = (e) => {
        e.preventDefault();
        post(route('central.tenants.store'));
    };

    return (
        <CentralLayout title="New blog">
            <Head title="New blog" />

            <form
                onSubmit={submit}
                className="rounded-xl bg-white px-6 shadow-sm ring-1 ring-slate-200"
            >
                <Section
                    title="Blog"
                    description="The company name and the subdomain its blog lives on."
                >
                    <div>
                        <InputLabel htmlFor="name" value="Name" />
                        <TextInput
                            id="name"
                            value={data.name}
                            className="mt-1 block w-full"
                            isFocused={true}
                            onChange={(e) => setData('name', e.target.value)}
                        />
                        <InputError message={errors.name} className="mt-2" />
                    </div>

                    <div>
                        <InputLabel htmlFor="subdomain" value="Domain" />
                        <div className="mt-1 flex rounded-md shadow-sm">
                            <TextInput
                                id="subdomain"
                                value={data.subdomain}
                                className="block w-full rounded-r-none"
                                placeholder="acme"
                                onChange={(e) =>
                                    setData('subdomain', e.target.value)
                                }
                            />
                            <span className="inline-flex items-center rounded-r-md border border-l-0 border-gray-300 bg-slate-50 px-3 text-sm text-slate-500">
                                .{baseDomain}
                            </span>
                        </div>
                        <InputError message={errors.subdomain} className="mt-2" />
                    </div>
                </Section>

                <Section
                    title="Branding"
                    description="Shown on the blog's public pages and writer dashboard."
                >
                    <div className="flex items-center gap-4">
                        <TenantAvatar
                            name={data.name || '?'}
                            logoUrl={logoPreview}
                            color={data.brand_color}
                            className="h-16 w-16"
                        />
                        <div className="flex-1">
                            <InputLabel htmlFor="logo" value="Logo" />
                            <input
                                id="logo"
                                type="file"
                                accept="image/png,image/jpeg,image/webp"
                                className="mt-1 block w-full text-sm text-slate-600 file:mr-4 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium hover:file:bg-slate-200"
                                onChange={(e) =>
                                    setData('logo', e.target.files[0] ?? null)
                                }
                            />
                            <p className="mt-1 text-xs text-slate-500">
                                PNG, JPG or WebP, up to 2 MB.
                            </p>
                            <InputError message={errors.logo} className="mt-2" />
                        </div>
                    </div>

                    <div>
                        <InputLabel htmlFor="brand_color" value="Brand color" />
                        <div className="mt-1 flex items-center gap-3">
                            <input
                                type="color"
                                aria-label="Pick brand color"
                                value={data.brand_color}
                                onChange={(e) =>
                                    setData('brand_color', e.target.value)
                                }
                                className="h-10 w-14 cursor-pointer rounded border border-gray-300"
                            />
                            <TextInput
                                id="brand_color"
                                value={data.brand_color}
                                className="block w-32 font-mono"
                                onChange={(e) =>
                                    setData('brand_color', e.target.value)
                                }
                            />
                        </div>
                        <InputError message={errors.brand_color} className="mt-2" />
                    </div>
                </Section>

                <Section
                    title="First writer"
                    description="This account can sign in to the blog's dashboard right away."
                >
                    <div>
                        <InputLabel htmlFor="owner_name" value="Name" />
                        <TextInput
                            id="owner_name"
                            value={data.owner_name}
                            className="mt-1 block w-full"
                            onChange={(e) =>
                                setData('owner_name', e.target.value)
                            }
                        />
                        <InputError message={errors.owner_name} className="mt-2" />
                    </div>

                    <div>
                        <InputLabel htmlFor="owner_email" value="Email" />
                        <TextInput
                            id="owner_email"
                            type="email"
                            value={data.owner_email}
                            className="mt-1 block w-full"
                            onChange={(e) =>
                                setData('owner_email', e.target.value)
                            }
                        />
                        <InputError message={errors.owner_email} className="mt-2" />
                    </div>

                    <div>
                        <InputLabel htmlFor="owner_password" value="Password" />
                        <TextInput
                            id="owner_password"
                            type="password"
                            value={data.owner_password}
                            className="mt-1 block w-full"
                            autoComplete="new-password"
                            onChange={(e) =>
                                setData('owner_password', e.target.value)
                            }
                        />
                        <InputError message={errors.owner_password} className="mt-2" />
                    </div>
                </Section>

                <div className="flex items-center justify-end gap-4 py-6">
                    <Link
                        href={route('central.tenants.index')}
                        className="text-sm font-medium text-slate-600 hover:text-slate-900"
                    >
                        Cancel
                    </Link>
                    <PrimaryButton disabled={processing}>
                        {processing ? 'Creating…' : 'Create blog'}
                    </PrimaryButton>
                </div>
            </form>
        </CentralLayout>
    );
}
