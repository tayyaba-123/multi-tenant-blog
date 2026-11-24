export default function TenantAvatar({ name, logoUrl, color, className = 'h-10 w-10' }) {
    if (logoUrl) {
        return (
            <img
                src={logoUrl}
                alt={`${name} logo`}
                className={`${className} rounded-lg object-contain`}
            />
        );
    }

    return (
        <span
            className={`${className} inline-flex items-center justify-center rounded-lg text-sm font-bold text-white`}
            style={{ backgroundColor: color }}
            aria-hidden="true"
        >
            {name.charAt(0).toUpperCase()}
        </span>
    );
}
