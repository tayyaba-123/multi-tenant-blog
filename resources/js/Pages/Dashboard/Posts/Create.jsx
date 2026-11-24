import PostForm from '@/Components/PostForm';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { fromLocalInputValue } from '@/lib/dates';
import { Head, useForm } from '@inertiajs/react';

export default function Create({ statuses }) {
    const { data, setData, post, processing, errors, transform } = useForm({
        title: '',
        body: '',
        status: 'draft',
        published_at: '',
    });

    transform((data) => ({
        ...data,
        published_at: fromLocalInputValue(data.published_at),
    }));

    const submit = (e) => {
        e.preventDefault();
        post(route('dashboard.posts.store'));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    New post
                </h2>
            }
        >
            <Head title="New post" />

            <div className="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
                <PostForm
                    data={data}
                    setData={setData}
                    errors={errors}
                    processing={processing}
                    statuses={statuses}
                    onSubmit={submit}
                    submitLabel="Create post"
                />
            </div>
        </AuthenticatedLayout>
    );
}
