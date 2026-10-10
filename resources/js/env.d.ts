/// <reference types="vite/client" />

declare module '*.vue' {
    import type { DefineComponent } from 'vue';
    const component: DefineComponent<Record<string, unknown>, Record<string, unknown>, unknown>;
    export default component;
}

interface Window {
    __LOCALE__?: string;
    __TRANSLATIONS__?: Record<string, unknown>;
    axios: import('axios').AxiosInstance;
    route: (name?: string, params?: unknown, absolute?: boolean) => string;
    Echo: unknown;
    Pusher: unknown;
}
