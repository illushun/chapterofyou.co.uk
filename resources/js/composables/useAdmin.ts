import { router } from '@inertiajs/vue3';

export function useAdmin() {
    /** Navigate to a paginated URL, preserving scroll & state. */
    function paginate(url: string | null) {
        if (url)
            router.get(url, {}, { preserveState: true, preserveScroll: true });
    }

    function confirmDelete(label: string, routeName: string, id: number) {
        if (confirm(`Delete "${label}"?\n\nThis cannot be undone.`)) {
            router.delete(route(routeName, id), { preserveScroll: true });
        }
    }

    function duplicateRecord(routeName: string, id: number) {
        router.post(route(routeName, id), {}, { preserveScroll: true });
    }

    /** Format a number as a GBP currency string. */
    function fmtCurrency(v: number | string | null | undefined): string {
        return `£${(Number(v) || 0).toFixed(2)}`;
    }

    /** Format a file size in bytes to a human-readable string. */
    function fmtSize(bytes: number): string {
        if (!bytes) return '0 B';
        const i = Math.floor(Math.log(bytes) / Math.log(1024));
        return `${(bytes / Math.pow(1024, i)).toFixed(1)} ${['B', 'KB', 'MB', 'GB'][i]}`;
    }

    function stockLabel(qty: number): { text: string; cls: string } {
        if (qty === 0) return { text: 'Out of stock', cls: 'adm-stock--nil' };
        if (qty < 10) return { text: `Low (${qty})`, cls: 'adm-stock--low' };
        return { text: String(qty), cls: 'adm-stock--ok' };
    }

    return {
        paginate,
        confirmDelete,
        duplicateRecord,
        fmtCurrency,
        fmtSize,
        stockLabel,
    };
}
