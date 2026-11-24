import BlogLayout from '@/Layouts/BlogLayout';
import { formatDate } from '@/lib/dates';
import { Head, Link } from '@inertiajs/react';

export default function Show({ post }) {
    const paragraphs = post.body.split(/\n\s*\n/);

    return (
        <BlogLayout>
            <Head title={post.title} />

            <article>
                <Link
                    href={route('home')}
                    className="text-sm font-medium text-gray-500 hover:text-gray-900"
                >
                    ← All posts
                </Link>

                <h1 className="mt-6 text-4xl font-bold tracking-tight">
                    {post.title}
                </h1>
                <p className="mt-3 text-sm text-gray-500">
                    <time dateTime={post.published_at}>
                        {formatDate(post.published_at)}
                    </time>
                    {post.author && <> · {post.author}</>}
                </p>

                <div className="mt-10 space-y-6 text-lg leading-relaxed text-gray-800">
                    {paragraphs.map((paragraph, index) => (
                        <p key={index} className="whitespace-pre-line">
                            {paragraph}
                        </p>
                    ))}
                </div>
            </article>
        </BlogLayout>
    );
}
