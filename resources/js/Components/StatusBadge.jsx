const styles = {
    draft: 'bg-gray-100 text-gray-700 ring-gray-200',
    scheduled: 'bg-amber-50 text-amber-800 ring-amber-200',
    published: 'bg-green-50 text-green-800 ring-green-200',
};

export default function StatusBadge({ status }) {
    return (
        <span
            className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize ring-1 ${styles[status]}`}
        >
            {status}
        </span>
    );
}
