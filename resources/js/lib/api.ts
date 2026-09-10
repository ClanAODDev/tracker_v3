function csrfToken(): string {
    return document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';
}

function xsrfToken(): string {
    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : '';
}

function redirectToLogin(): never {
    window.location.assign('/login?expired=1');
    throw new Error('Your session has expired.');
}

function guardAuth(response: Response): void {
    if (response.status === 401 || response.status === 419) {
        redirectToLogin();
    }
    if (response.ok) {
        window.dispatchEvent(new Event('session:touch'));
    }
}

export async function postJson<T = unknown>(url: string, body: Record<string, unknown>): Promise<T> {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-XSRF-TOKEN': xsrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: JSON.stringify(body),
    });

    guardAuth(response);

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
        throw new Error((data as { message?: string }).message ?? 'Request failed');
    }

    return data as T;
}

export async function getJson<T = unknown>(url: string): Promise<T> {
    const response = await fetch(url, {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
    });

    guardAuth(response);

    if (!response.ok) {
        throw new Error('Request failed');
    }

    return (await response.json()) as T;
}
