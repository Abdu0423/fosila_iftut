import { api } from '../api/client';

export interface CachedPage {
    component: string;
    props: Record<string, unknown>;
}

interface RedirectPage {
    redirect: string;
}

type FetchResult = CachedPage | RedirectPage;

const pages = import.meta.glob('../Pages/**/*.vue');
const components = new Map<string, unknown>();
const cache = new Map<string, CachedPage>();
const inflight = new Map<string, Promise<FetchResult>>();
const latest = new Map<string, number>();
const MAX_PAGES = 24;
let serial = 0;

function clone<T>(value: T): T {
    if (typeof structuredClone === 'function') {
        return structuredClone(value);
    }

    return JSON.parse(JSON.stringify(value)) as T;
}

export function readPage(key: string): CachedPage | undefined {
    const page = cache.get(key);

    if (!page) {
        return undefined;
    }

    return {
        component: page.component,
        props: clone(page.props),
    };
}

export function forgetPage(key?: string): void {
    if (key) {
        cache.delete(key);
        return;
    }

    cache.clear();
}

function remember(key: string, page: CachedPage): void {
    cache.delete(key);
    cache.set(key, {
        component: page.component,
        props: clone(page.props),
    });

    while (cache.size > MAX_PAGES) {
        const oldest = cache.keys().next().value;
        if (!oldest) {
            break;
        }
        cache.delete(oldest);
    }
}

export async function resolvePageComponent(name: string): Promise<unknown> {
    const cached = components.get(name);
    if (cached) {
        return cached;
    }

    const loader = pages[`../Pages/${name}.vue`] as (() => Promise<{ default: unknown }>) | undefined;
    const fallback = pages['../Pages/Errors/404.vue'] as () => Promise<{ default: unknown }>;
    const component = (await (loader || fallback)()).default;
    components.set(name, component);

    return component;
}

export function fetchPage(
    path: string,
    query: Record<string, unknown>,
    key: string,
    fresh = false,
): Promise<FetchResult> {
    if (!fresh) {
        const pending = inflight.get(key);
        if (pending) {
            return pending;
        }
    }

    const requestId = ++serial;
    latest.set(key, requestId);

    const request = api.get(path, { params: query })
        .then(async (response) => {
            const data = response.data ?? {};

            if (data.redirect && !data.component) {
                return { redirect: String(data.redirect) } as RedirectPage;
            }

            const page: CachedPage = {
                component: data.component || 'Errors/404',
                props: data.props && !Array.isArray(data.props) ? data.props : {},
            };

            if (latest.get(key) === requestId) {
                remember(key, page);
            }
            await resolvePageComponent(page.component);

            return page;
        })
        .finally(() => {
            if (inflight.get(key) === request) {
                inflight.delete(key);
            }
        });

    inflight.set(key, request);

    return request;
}

export function prefetchPage(url: string | undefined | null): Promise<void> {
    if (!url || url.startsWith('http') || url.startsWith('mailto:')) {
        return Promise.resolve();
    }

    const path = url.split('?')[0] || '/';
    const pending = inflight.get(url);
    if (cache.has(url)) {
        return Promise.resolve();
    }
    if (pending) {
        return pending.then(() => undefined).catch(() => undefined);
    }

    const query: Record<string, string> = {};
    const search = url.includes('?') ? url.slice(url.indexOf('?') + 1) : '';
    new URLSearchParams(search).forEach((value, name) => {
        query[name] = value;
    });

    return fetchPage(path, query, url).then(() => undefined).catch(() => undefined);
}

export function isRedirect(result: FetchResult): result is RedirectPage {
    return 'redirect' in result;
}
