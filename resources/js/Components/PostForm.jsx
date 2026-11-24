import CoverImageInput from '@/Components/CoverImageInput';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Link } from '@inertiajs/react';

export default function PostForm({
    data,
    setData,
    errors,
    processing,
    statuses,
    onSubmit,
    submitLabel,
    currentCoverUrl = null,
}) {
    return (
        <form
            onSubmit={onSubmit}
            className="space-y-6 rounded-lg bg-white p-6 shadow-sm"
        >
            <div>
                <InputLabel htmlFor="title" value="Title" />
                <TextInput
                    id="title"
                    value={data.title}
                    className="mt-1 block w-full text-lg"
                    isFocused={true}
                    onChange={(e) => setData('title', e.target.value)}
                />
                <InputError message={errors.title} className="mt-2" />
            </div>

            <CoverImageInput
                file={data.cover_image}
                currentUrl={currentCoverUrl}
                removed={data.remove_cover_image}
                onChange={(file) => {
                    setData((data) => ({
                        ...data,
                        cover_image: file,
                        remove_cover_image: false,
                    }));
                }}
                onRemove={() => {
                    setData((data) => ({
                        ...data,
                        cover_image: null,
                        remove_cover_image: true,
                    }));
                }}
                error={errors.cover_image}
            />

            <div>
                <InputLabel htmlFor="body" value="Body" />
                <textarea
                    id="body"
                    rows={14}
                    value={data.body}
                    onChange={(e) => setData('body', e.target.value)}
                    className="mt-1 block w-full rounded-md border-gray-300 leading-relaxed shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
                <p className="mt-1 text-xs text-gray-500">
                    Plain text. Leave a blank line between paragraphs.
                </p>
                <InputError message={errors.body} className="mt-2" />
            </div>

            <fieldset>
                <legend className="block text-sm font-medium text-gray-700">
                    Status
                </legend>
                <div className="mt-2 grid gap-3 sm:grid-cols-3">
                    {statuses.map((status) => (
                        <label
                            key={status.value}
                            className={
                                'flex cursor-pointer items-center gap-2 rounded-md border px-3 py-2 text-sm ' +
                                (data.status === status.value
                                    ? 'border-indigo-500 bg-indigo-50 text-indigo-900'
                                    : 'border-gray-300 text-gray-700 hover:border-gray-400')
                            }
                        >
                            <input
                                type="radio"
                                name="status"
                                value={status.value}
                                checked={data.status === status.value}
                                onChange={(e) =>
                                    setData('status', e.target.value)
                                }
                                className="text-indigo-600 focus:ring-indigo-500"
                            />
                            {status.label}
                        </label>
                    ))}
                </div>
                <InputError message={errors.status} className="mt-2" />
            </fieldset>

            {data.status === 'scheduled' && (
                <div>
                    <InputLabel htmlFor="published_at" value="Publish at" />
                    <TextInput
                        id="published_at"
                        type="datetime-local"
                        value={data.published_at}
                        className="mt-1 block"
                        onChange={(e) =>
                            setData('published_at', e.target.value)
                        }
                    />
                    <p className="mt-1 text-xs text-gray-500">
                        Your local time. The post goes live automatically.
                    </p>
                    <InputError message={errors.published_at} className="mt-2" />
                </div>
            )}

            <div className="flex items-center justify-end gap-4 border-t border-gray-100 pt-6">
                <Link
                    href={route('dashboard.posts.index')}
                    className="text-sm font-medium text-gray-600 hover:text-gray-900"
                >
                    Cancel
                </Link>
                <PrimaryButton disabled={processing}>{submitLabel}</PrimaryButton>
            </div>
        </form>
    );
}
