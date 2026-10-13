import type { AxiosError, InternalAxiosRequestConfig } from 'axios';

type RetryConfig = InternalAxiosRequestConfig & { _retried?: boolean };

export function rewritePanelPath(url: string): string {
    return url;
}

function toApiUrl(url: string): string {
    if (!url || url.startsWith('http://') || url.startsWith('https://')) {
        return url;
    }

    url = rewritePanelPath(url);

    if (
        url.startsWith('/api/')
        || url === '/api'
        || url.startsWith('/sanctum')
        || url.startsWith('/broadcasting')
        || url.startsWith('/build')
        || url.startsWith('/storage')
    ) {
        return url;
    }

    if (url === '/') {
        return '/api';
    }

    if (url.startsWith('/')) {
        return `/api${url}`;
    }

    return url;
}

export function toSpaPath(target: string): string {
    if (!target) {
        return '/';
    }

    let path = target;

    if (target.startsWith('http://') || target.startsWith('https://')) {
        const url = new URL(target);
        path = `${url.pathname}${url.search}`;
    }

    if (path.startsWith('/api/')) {
        path = path.slice(4) || '/';
    } else if (path === '/api') {
        path = '/';
    }

    return path.startsWith('/') ? path : `/${path}`;
}

export function validationErrors(error: unknown): Record<string, string[]> {
    const response = (error as AxiosError<{ errors?: Record<string, string[]> }>)?.response;
    const errors = response?.data?.errors;

    if (!errors) {
        return {};
    }

    return errors;
}

export function setupApi(): void {
    const http = window.axios;

    http.interceptors.request.use((config) => {
        if (typeof config.url === 'string') {
            config.url = toApiUrl(config.url);
        }

        config.headers.set('X-Spa', '1');
        config.headers.set('Accept', 'application/json');

        return config;
    });

    http.interceptors.response.use(
        (response) => response,
        async (error: AxiosError) => {
            const config = error.config as RetryConfig | undefined;
            const status = error.response?.status;

            if (status === 419 && config && !config._retried) {
                config._retried = true;
                await http.get('/sanctum/csrf-cookie');
                return http.request(config);
            }

            if (status === 401 && !window.location.pathname.startsWith('/login')) {
                const { default: router } = await import('../router');
                await router.replace('/login');
            }

            return Promise.reject(error);
        },
    );
}

export const api = new Proxy({} as typeof window.axios, {
    get(_target, prop) {
        const value = window.axios[prop as keyof typeof window.axios];
        return typeof value === 'function' ? value.bind(window.axios) : value;
    },
});
