import TenantAvatar from '@/Components/TenantAvatar';
import { usePage } from '@inertiajs/react';

// Pick black or white text for legible contrast on the brand color.
function contrastColor(hex) {
    const value = hex.replace('#', '');
    const [r, g, b] = [0, 2, 4].map((i) => parseInt(value.slice(i, i + 2), 16));
    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;

    return luminance > 0.6 ? '#111827' : '#ffffff';
}

export function BrandProvider({ className = '', children }) {
    const { tenant } = usePage().props;
    const color = tenant?.brand_color ?? '#4f46e5';

    return (
        <div
            className={className}
            style={{
                '--brand': color,
                '--brand-contrast': contrastColor(color),
            }}
        >
            {children}
        </div>
    );
}

export function BrandMark({ className = 'h-9 w-9' }) {
    const { tenant } = usePage().props;

    return (
        <span className="flex items-center gap-3">
            <TenantAvatar
                name={tenant.name}
                logoUrl={tenant.logo_url}
                color={tenant.brand_color}
                className={className}
            />
            <span className="text-lg font-bold tracking-tight">
                {tenant.name}
            </span>
        </span>
    );
}
