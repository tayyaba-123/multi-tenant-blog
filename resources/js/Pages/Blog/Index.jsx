import BlogLayout from '@/Layouts/BlogLayout';
import { formatDate } from '@/lib/dates';
import { Head, Link, usePage } from '@inertiajs/react';

export default function Index({ posts }) {
    const { tenant } = usePage().props;

    return (
        <BlogLayout>
            <Head title={tenant.name} />

            <h1 className="text-4xl font-bold tracking-tight">
                Latest from {tenant.name}
            </h1>

            {posts.data.length === 0 ? (
                <p className="mt-12 text-gray-500">
                    Nothing published yet. Check back soon.
                </p>
            ) : (
                <div className="mt-12 space-y-12">
                    {posts.data.map((post) => (
                        <article key={post.id}>
                            <p className="text-sm text-gray-500">
                                <time dateTime={post.published_at}>
                                    {formatDate(post.published_at)}
                                </time>
                                {post.author && <> · {post.author}</>}
                            </p>
                            <h2 className="mt-2 text-2xl font-semibold tracking-tight">
                                <Link
                                    href={route('blog.show', post.slug)}
                                    className="hover:underline"
                                >
                                    {post.title}
                                </Link>
                            </h2>
                            <p className="mt-3 leading-relaxed text-gray-600">
                                {post.excerpt}
                            </p>
                        </article>
                    ))}
                </div>
            )}

            {posts.last_page > 1 && (
                <nav className="mt-16 flex justify-between text-sm font-medium">
                    {posts.prev_page_url ? (
                        <Link href={posts.prev_page_url}>← Newer posts</Link>
                    ) : (
                        <span />
                    )}
                    {posts.next_page_url && (
                        <Link href={posts.next_page_url}>Older posts →</Link>
                    )}
                </nav>
            )}
        </BlogLayout>
    );
}
