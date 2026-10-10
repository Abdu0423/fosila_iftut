import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { createI18n } from 'vue-i18n';
import { VueMaskDirective } from 'v-mask';
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import 'vuetify/styles';
import '../css/app.css';
import App from './App.vue';
import router from './router';
import { setupApi } from './api/client';
import { installRouteHelper } from './router/laravel';
import { usePageStore } from './stores/page';

installRouteHelper();
setupApi();

const vuetify = createVuetify({
    components,
    directives,
    defaults: {
        VBtn: {
            elevation: 0,
            style: 'text-transform: none; letter-spacing: 0;',
        },
        VCard: {
            elevation: 0,
        },
        VTextField: {
            variant: 'outlined',
            density: 'comfortable',
            color: 'primary',
        },
        VSelect: {
            variant: 'outlined',
            density: 'comfortable',
            color: 'primary',
        },
        VTextarea: {
            variant: 'outlined',
            density: 'comfortable',
            color: 'primary',
        },
        VAutocomplete: {
            variant: 'outlined',
            density: 'comfortable',
            color: 'primary',
        },
        VCombobox: {
            variant: 'outlined',
            density: 'comfortable',
            color: 'primary',
        },
        VDataTable: {
            density: 'comfortable',
        },
        VChip: {
            rounded: 'lg',
        },
    },
    theme: {
        defaultTheme: 'fosila',
        themes: {
            fosila: {
                dark: false,
                colors: {
                    background: '#F4F1EC',
                    surface: '#FFFFFF',
                    primary: '#3D4FD8',
                    secondary: '#12151C',
                    accent: '#5B6CFF',
                    error: '#DC4C4C',
                    info: '#3B82F6',
                    success: '#168A6A',
                    warning: '#D97706',
                    'on-background': '#1C1917',
                    'on-surface': '#1C1917',
                },
            },
        },
    },
});

const initialLocale = window.__LOCALE__ || 'ru';
const initialTranslations = window.__TRANSLATIONS__ || {};

const i18n = createI18n({
    legacy: false,
    locale: initialLocale,
    fallbackLocale: 'ru',
    messages: {
        [initialLocale]: initialTranslations,
    },
    silentTranslationWarn: true,
    silentFallbackWarn: true,
    missingWarn: false,
    fallbackWarn: false,
});

const app = createApp(App);

app.directive('mask', VueMaskDirective);
app.config.globalProperties.route = window.route;

app.use(createPinia());
app.use(router);
app.use(vuetify);
app.use(i18n);

usePageStore().props.translations = initialTranslations;
usePageStore().props.locale = initialLocale;

app.mount('#app');
