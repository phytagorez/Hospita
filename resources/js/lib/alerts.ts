type AlertOptions = {
    icon: 'success' | 'error' | 'warning';
    titleText: string;
    text?: string;
    toast?: boolean;
    position?: 'bottom-end';
    timer?: number;
    showCancelButton?: boolean;
    showConfirmButton?: boolean;
    confirmButtonText?: string;
    cancelButtonText?: string;
    confirmButtonColor?: string;
    focusCancel?: boolean;
};

declare global {
    interface Window {
        Swal?: {
            fire: (options: AlertOptions) => Promise<{ isConfirmed: boolean }>;
        };
    }
}

export function showSuccess(message: string) {
    if (window.Swal) {
        void window.Swal.fire({
            icon: 'success',
            titleText: message,
            toast: true,
            position: 'bottom-end',
            timer: 4500,
            showConfirmButton: false,
        });
        return;
    }

    document.getElementById('hospia-success-toast')?.remove();
    const toast = document.createElement('div');
    toast.id = 'hospia-success-toast';
    toast.className = 'fallback-toast';
    toast.setAttribute('role', 'status');
    document.body.append(toast);
    toast.textContent = message;
    window.setTimeout(() => toast.remove(), 4500);
}

export function showError(message: string) {
    if (window.Swal) {
        void window.Swal.fire({
            icon: 'error',
            titleText: 'Periksa kembali',
            text: message,
            confirmButtonText: 'Tutup',
            confirmButtonColor: '#1d4ed8',
        });
    } else {
        window.alert(message);
    }
}

export async function confirmAction(
    title: string,
    message: string,
    confirmLabel: string,
) {
    if (!window.Swal) return window.confirm(title + '\n\n' + message);

    const result = await window.Swal.fire({
        icon: 'warning',
        titleText: title,
        text: message,
        showCancelButton: true,
        focusCancel: true,
        confirmButtonText: confirmLabel,
        cancelButtonText: 'Kembali',
        confirmButtonColor: '#be123c',
    });
    return result.isConfirmed;
}
