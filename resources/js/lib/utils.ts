import { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function urlIsActive(
    urlToCheck: NonNullable<InertiaLinkProps['href']>,
    currentUrl: string,
) {
    return toUrl(urlToCheck) === currentUrl;
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}

export function formatDate(date: string) {
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

export function formatDateTime(date: string) {
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: 'numeric',
        minute: 'numeric',
    });
}

export function formatDateTimeToUnix(date: string) {
    return new Date(date).getTime();
}

export function formatDateDiff(date: string) {
    const diff = new Date(date).getTime() - new Date().getTime();
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    return days;
}

export const formatDateForPicker = (dateString: string | null | undefined): string | null => {
    if (!dateString) return null;
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return null;

        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        const seconds = String(date.getSeconds()).padStart(2, '0');

        return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
    } catch {
        return null;
    }
};

export function now() {
    return new Date().getTime();
}

export function isFuture(date: string) {
    return new Date(date).getTime() > new Date().getTime();
}

export function isPast(date: string) {
    return new Date(date).getTime() < new Date().getTime();
}

export function isLive(start: string, end?: string) {
    return new Date(start).getTime() < now() && (end ? new Date(end).getTime() > now() : true);
}
