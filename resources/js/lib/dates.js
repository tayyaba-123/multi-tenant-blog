// Convert an ISO timestamp to the "YYYY-MM-DDTHH:mm" value a
// datetime-local input expects, in the browser's timezone.
export function toLocalInputValue(iso) {
    if (!iso) {
        return '';
    }

    const date = new Date(iso);
    const offset = date.getTimezoneOffset() * 60000;

    return new Date(date.getTime() - offset).toISOString().slice(0, 16);
}

// Convert a datetime-local value (browser timezone) to an ISO UTC string.
export function fromLocalInputValue(value) {
    return value ? new Date(value).toISOString() : null;
}

export function formatDateTime(iso) {
    return iso
        ? new Date(iso).toLocaleString(undefined, {
              dateStyle: 'medium',
              timeStyle: 'short',
          })
        : '';
}

export function formatDate(iso) {
    return iso
        ? new Date(iso).toLocaleDateString(undefined, { dateStyle: 'long' })
        : '';
}
