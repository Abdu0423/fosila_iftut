import { defineStore } from 'pinia';

const SHARED_KEYS = new Set([
    'auth',
    'flash',
    'errors',
    'locale',
    'unreadChatsCount',
    'translations',
]);

export interface FlashMessages {
    success: string | null;
    error: string | null;
    warning: string | null;
    info: string | null;
}

export interface AuthUser {
    id: number;
    name: string;
    email: string;
    role: string | null;
    locale?: string;
}

let reloadResolve: (() => void) | null = null;

export function signalPageLoaded(): void {
    reloadResolve?.();
    reloadResolve = null;
}

export function waitForReload(): Promise<void> {
    return new Promise((resolve) => {
        reloadResolve = resolve;
    });
}

export const usePageStore = defineStore('page', {
    state: () => ({
        component: '',
        reloadToken: 0,
        props: {
            auth: { user: null as AuthUser | null },
            flash: {
                success: null,
                error: null,
                warning: null,
                info: null,
            } as FlashMessages,
            errors: {} as Record<string, string>,
            locale: 'ru',
            unreadChatsCount: 0,
            translations: {} as Record<string, unknown>,
        } as Record<string, unknown>,
    }),
    actions: {
        setPage(component: string, data: Record<string, unknown> = {}) {
            for (const key of Object.keys(this.props)) {
                if (!SHARED_KEYS.has(key)) {
                    delete this.props[key];
                }
            }

            Object.assign(this.props, data);
            this.component = component;
        },
        setFlash(flash: Partial<FlashMessages> | null) {
            this.props.flash = {
                success: flash?.success ?? null,
                error: flash?.error ?? null,
                warning: flash?.warning ?? null,
                info: flash?.info ?? null,
            };
        },
        setAuth(user: AuthUser | null, locale: string, unreadChatsCount: number) {
            this.props.auth = { user };
            this.props.locale = locale;
            this.props.unreadChatsCount = unreadChatsCount;
        },
    },
});
