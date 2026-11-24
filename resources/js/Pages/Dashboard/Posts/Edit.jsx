import PostForm from '@/Components/PostForm';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { fromLocalInputValue, toLocalInputValue } from '@/lib/dates';
import { Head, useForm } from '@inertiajs/react';

export default function Edit({ post, statuses }) {
    const { data, setData, put, processing, errors, transform } = useForm({
        title: post.title,
        body: post.body,
        status: post.status,
        published_at: toLocalInputValue(post.published_at),
    });

    transform((data) => ({
        ...data,
        published_at: fromLocalInputValue(data.published_at),
    }));

    const submit = (e) => {
        e.preventDefault();
        put(route('dashboard.posts.update', post.id));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Edit post
                </h2>
            }
        >
            <Head title={`Edit ${post.title}`} />

            <div className="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
                <PostForm
                    data={data}
                    setData={setData}
                    errors={errors}
                    processing={processing}
                    statuses={statuses}
                    onSubmit={submit}
                    submitLabel="Save changes"
                />
            </div>
        </AuthenticatedLayout>
    );
}
