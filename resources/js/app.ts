import './bootstrap';
import '../css/app.css';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { createI18n } from 'vue-i18n';
import { VueMaskDirective } from 'v-mask';
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import 'vuetify/styles';
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
    theme: {
        defaultTheme: 'light',
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
