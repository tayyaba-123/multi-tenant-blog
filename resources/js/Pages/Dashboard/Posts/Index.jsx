import StatusBadge from '@/Components/StatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { formatDateTime } from '@/lib/dates';
import { Head, Link, router } from '@inertiajs/react';

function PostDate({ post }) {
    if (post.status === 'draft') {
        return (
            <span className="text-gray-400">
                Edited {formatDateTime(post.updated_at)}
            </span>
        );
    }

    return (
        <span>
            {post.status === 'scheduled' ? 'Goes live ' : 'Published '}
            {formatDateTime(post.published_at)}
        </span>
    );
}

export default function Index({ posts }) {
    const destroy = (post) => {
        if (confirm(`Delete "${post.title}"? This can't be undone.`)) {
            router.delete(route('dashboard.posts.destroy', post.id), {
                preserveScroll: true,
            });
        }
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Posts
                    </h2>
                    <Link
                        href={route('dashboard.posts.create')}
                        className="rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700"
                    >
                        New post
                    </Link>
                </div>
            }
        >
            <Head title="Posts" />

            <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                {posts.data.length === 0 ? (
                    <div className="rounded-lg border border-dashed border-gray-300 bg-white p-12 text-center">
                        <p className="text-gray-600">No posts yet.</p>
                        <Link
                            href={route('dashboard.posts.create')}
                            className="mt-3 inline-block text-sm font-medium text-indigo-600 hover:underline"
                        >
                            Write the first one
                        </Link>
                    </div>
                ) : (
                    <ul className="divide-y divide-gray-100 overflow-hidden rounded-lg bg-white shadow-sm">
                        {posts.data.map((post) => (
                            <li
                                key={post.id}
                                className="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <div className="flex min-w-0 items-center gap-4">
                                    {post.cover_image_url ? (
                                        <img
                                            src={post.cover_image_url}
                                            alt=""
                                            className="h-12 w-20 shrink-0 rounded object-cover"
                                        />
                                    ) : (
                                        <div className="h-12 w-20 shrink-0 rounded bg-gray-100" />
                                    )}
                                    <div className="min-w-0">
                                        <div className="flex items-center gap-3">
                                            <StatusBadge status={post.status} />
                                            <p className="truncate font-medium text-gray-900">
                                                {post.title}
                                            </p>
                                        </div>
                                        <p className="mt-1 text-sm text-gray-500">
                                            {post.author ?? 'Former writer'} ·{' '}
                                            <PostDate post={post} />
                                        </p>
                                    </div>
                                </div>

                                <div className="flex shrink-0 items-center gap-4 text-sm font-medium">
                                    {post.status === 'published' && (
                                        <Link
                                            href={route('blog.show', post.slug)}
                                            className="text-gray-600 hover:text-gray-900"
                                        >
                                            View
                                        </Link>
                                    )}
                                    {post.can.update && (
                                        <Link
                                            href={route(
                                                'dashboard.posts.edit',
                                                post.id,
                                            )}
                                            className="text-indigo-600 hover:text-indigo-800"
                                        >
                                            Edit
                                        </Link>
                                    )}
                                    {post.can.delete && (
                                        <button
                                            type="button"
                                            onClick={() => destroy(post)}
                                            className="text-red-600 hover:text-red-800"
                                        >
                                            Delete
                                        </button>
                                    )}
                                </div>
                            </li>
                        ))}
                    </ul>
                )}

                {posts.last_page > 1 && (
                    <nav className="mt-6 flex justify-between text-sm">
                        {posts.prev_page_url ? (
                            <Link
                                href={posts.prev_page_url}
                                className="text-indigo-600"
                            >
                                Previous
                            </Link>
                        ) : (
                            <span />
                        )}
                        {posts.next_page_url && (
                            <Link
                                href={posts.next_page_url}
                                className="text-indigo-600"
                            >
                                Next
                            </Link>
                        )}
                    </nav>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
