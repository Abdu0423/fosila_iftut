import { defineStore } from 'pinia';
import { api } from '../api/client';
import { usePageStore, type AuthUser } from './page';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null as AuthUser | null,
        locale: (window.__LOCALE__ || 'ru') as string,
        unreadChatsCount: 0,
        ready: false,
    }),
    actions: {
        async fetch() {
            try {
                const { data } = await api.get('/api/me');
                this.user = data.user;
                this.locale = data.locale || this.locale;
                this.unreadChatsCount = data.unreadChatsCount || 0;
            } catch {
                this.user = null;
            } finally {
                this.ready = true;
                usePageStore().setAuth(this.user, this.locale, this.unreadChatsCount);
            }
        },
    },
});
