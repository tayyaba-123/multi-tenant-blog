import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import { useEffect, useState } from 'react';

export default function CoverImageInput({
    file,
    currentUrl,
    removed,
    onChange,
    onRemove,
    error,
}) {
    const [preview, setPreview] = useState(null);

    useEffect(() => {
        if (!file) {
            setPreview(null);
            return;
        }

        const url = URL.createObjectURL(file);
        setPreview(url);

        return () => URL.revokeObjectURL(url);
    }, [file]);

    const shown = preview ?? (removed ? null : currentUrl);

    return (
        <div>
            <InputLabel htmlFor="cover_image" value="Cover image" />

            {shown && (
                <div className="relative mt-2 overflow-hidden rounded-lg ring-1 ring-gray-200">
                    <img
                        src={shown}
                        alt="Cover preview"
                        className="aspect-[2/1] w-full object-cover"
                    />
                    <button
                        type="button"
                        onClick={onRemove}
                        className="absolute right-2 top-2 rounded-md bg-white/90 px-2 py-1 text-xs font-medium text-red-700 shadow hover:bg-white"
                    >
                        Remove
                    </button>
                </div>
            )}

            <input
                id="cover_image"
                type="file"
                accept="image/png,image/jpeg,image/webp"
                className="mt-2 block w-full text-sm text-gray-600 file:mr-4 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium hover:file:bg-gray-200"
                onChange={(e) => onChange(e.target.files[0] ?? null)}
            />
            <p className="mt-1 text-xs text-gray-500">
                JPG, PNG or WebP, up to 4 MB. Wide images work best.
            </p>
            <InputError message={error} className="mt-2" />
        </div>
    );
}
