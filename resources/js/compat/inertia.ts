import { reactive } from 'vue';
import vueRouter from '../router';
import { api, rewritePanelPath, toSpaPath, validationErrors } from '../api/client';
import { usePageStore, waitForReload } from '../stores/page';
import { useAuthStore } from '../stores/auth';

type FormOptions = {
    onSuccess?: (page: { props: Record<string, unknown> }) => void;
    onError?: (errors: Record<string, string | string[]>) => void;
    onFinish?: () => void;
    forceFormData?: boolean;
    preserveState?: boolean;
    preserveScroll?: boolean;
};

type VisitOptions = FormOptions & {
    method?: string;
    data?: Record<string, unknown>;
    only?: string[];
};

const META_KEYS = new Set([
    'errors',
    'processing',
    'progress',
    'hasErrors',
    'recentlySuccessful',
    'wasSuccessful',
]);

function splitUrl(input: string): { path: string; query: Record<string, string> } {
    const url = new URL(input, window.location.origin);
    const query: Record<string, string> = {};
    url.searchParams.forEach((value, key) => {
        query[key] = value;
    });

    return { path: url.pathname, query };
}

function cleanQuery(data: Record<string, unknown> = {}): Record<string, string> {
    const query: Record<string, string> = {};

    Object.entries(data).forEach(([key, value]) => {
        if (value !== null && value !== undefined && value !== '') {
            query[key] = String(value);
        }
    });

    return query;
}

function containsFile(value: unknown): boolean {
    if (value instanceof File || value instanceof Blob) {
        return true;
    }

    if (Array.isArray(value)) {
        return value.some((item) => containsFile(item));
    }

    if (value && typeof value === 'object') {
        return Object.values(value as Record<string, unknown>).some((item) => containsFile(item));
    }

    return false;
}

function appendFormData(formData: FormData, key: string, value: unknown): void {
    if (value === null || value === undefined) {
        return;
    }

    if (value instanceof File || value instanceof Blob) {
        formData.append(key, value);
        return;
    }

    if (Array.isArray(value)) {
        value.forEach((item, index) => appendFormData(formData, `${key}[${index}]`, item));
        return;
    }

    if (typeof value === 'object') {
        Object.entries(value as Record<string, unknown>).forEach(([child, childValue]) => {
            appendFormData(formData, `${key}[${child}]`, childValue);
        });
        return;
    }

    formData.append(key, String(value));
}

function payloadOf(form: Record<string, unknown>, transformer: ((data: Record<string, unknown>) => Record<string, unknown>) | null) {
    const data: Record<string, unknown> = {};

    Object.entries(form).forEach(([key, value]) => {
        if (META_KEYS.has(key) || typeof value === 'function') {
            return;
        }

        data[key] = value;
    });

    return transformer ? transformer(data) : data;
}

async function followResponse(data: { redirect?: string; flash?: Record<string, string | null>; component?: string; props?: Record<string, unknown> }) {
    const pageStore = usePageStore();

    if (data?.flash) {
        pageStore.setFlash(data.flash);
    }

    if (data?.component) {
        pageStore.setPage(data.component, data.props || {});
    }

    if (data?.redirect) {
        await useAuthStore().fetch();
        await vueRouter.push(toSpaPath(data.redirect));
    }
}

async function send(method: string, url: string, data: Record<string, unknown>, options: FormOptions = {}) {
    url = rewritePanelPath(url);
    const pageStore = usePageStore();
    let verb = method.toLowerCase();
    let body: Record<string, unknown> | FormData = data;

    if (options.forceFormData || containsFile(data)) {
        const formData = new FormData();
        Object.entries(data).forEach(([key, value]) => appendFormData(formData, key, value));

        if (verb !== 'post') {
            formData.append('_method', verb.toUpperCase());
            verb = 'post';
        }

        body = formData;
    }

    try {
        const response = await api.request({
            url,
            method: verb,
            data: body,
        });
        const responseData = response.data || {};
        const page = {
            props: {
                ...pageStore.props,
                errors: {},
                ...(responseData.props || {}),
            },
        };

        options.onSuccess?.(page);
        await followResponse(responseData);
        return responseData;
    } catch (error) {
        const raw = validationErrors(error);
        options.onError?.(raw);
        throw error;
    } finally {
        options.onFinish?.();
    }
}

export function usePage() {
    const pageStore = usePageStore();

    return {
        get props() {
            return pageStore.props;
        },
        get url() {
            return vueRouter.currentRoute.value.fullPath;
        },
        get component() {
            return pageStore.component;
        },
    };
}

export function useForm<T extends Record<string, unknown>>(defaults: T) {
    const original = JSON.parse(JSON.stringify(defaults)) as T;
    let transformer: ((data: Record<string, unknown>) => Record<string, unknown>) | null = null;

    const form = reactive({
        ...defaults,
        errors: {} as Record<string, string>,
        processing: false,
        progress: null as null | { percentage: number },
        hasErrors: false,
        recentlySuccessful: false,
        wasSuccessful: false,
        transform(callback: (data: Record<string, unknown>) => Record<string, unknown>) {
            transformer = callback;
            return form;
        },
        reset(...fields: string[]) {
            const keys = fields.length ? fields : Object.keys(original);
            keys.forEach((key) => {
                if (key in original) {
                    (form as Record<string, unknown>)[key] = original[key];
                }
            });
        },
        clearErrors(...fields: string[]) {
            if (!fields.length) {
                Object.keys(form.errors).forEach((key) => delete form.errors[key]);
            } else {
                fields.forEach((key) => delete form.errors[key]);
            }
            form.hasErrors = Object.keys(form.errors).length > 0;
        },
        data() {
            return payloadOf(form as unknown as Record<string, unknown>, null);
        },
        post(url: string, options?: FormOptions) {
            return submit('post', url, options);
        },
        put(url: string, options?: FormOptions) {
            return submit('put', url, options);
        },
        patch(url: string, options?: FormOptions) {
            return submit('patch', url, options);
        },
        delete(url: string, options?: FormOptions) {
            return submit('delete', url, options);
        },
    });

    async function submit(method: string, url: string, options: FormOptions = {}) {
        form.processing = true;
        form.clearErrors();
        form.wasSuccessful = false;

        try {
            const data = payloadOf(form as unknown as Record<string, unknown>, transformer);
            await send(method, url, data, {
                ...options,
                onError: (errors) => {
                    Object.entries(errors).forEach(([key, value]) => {
                        form.errors[key] = Array.isArray(value) ? value[0] : value;
                    });
                    form.hasErrors = Object.keys(form.errors).length > 0;
                    options.onError?.(errors);
                },
            });
            form.wasSuccessful = true;
            form.recentlySuccessful = true;
        } catch {
            form.wasSuccessful = false;
        } finally {
            form.processing = false;
        }
    }

    return form;
}

export const router = {
    visit(url: string, options: VisitOptions = {}) {
        const method = (options.method || 'get').toLowerCase();
        url = rewritePanelPath(url);

        if (method === 'get') {
            const { path, query } = splitUrl(url);
            return vueRouter.push({
                path,
                query: { ...query, ...cleanQuery(options.data || {}) },
            }).finally(() => options.onFinish?.());
        }

        return send(method, url, options.data || {}, options);
    },
    get(url: string, data: Record<string, unknown> = {}, options: VisitOptions = {}) {
        const { path, query } = splitUrl(rewritePanelPath(url));
        return vueRouter.push({
            path,
            query: { ...query, ...cleanQuery(data) },
        }).finally(() => options.onFinish?.());
    },
    post(url: string, data: Record<string, unknown> = {}, options: FormOptions = {}) {
        return send('post', url, data, options);
    },
    put(url: string, data: Record<string, unknown> = {}, options: FormOptions = {}) {
        return send('put', url, data, options);
    },
    patch(url: string, data: Record<string, unknown> = {}, options: FormOptions = {}) {
        return send('patch', url, data, options);
    },
    delete(url: string, options: FormOptions = {}) {
        return send('delete', url, {}, options);
    },
    reload(_options: VisitOptions = {}) {
        const pending = waitForReload();
        usePageStore().reloadToken += 1;
        return pending;
    },
};

export function Head() {
    return null;
}

export function Link() {
    return null;
}
