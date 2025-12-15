import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { Ziggy } from './ziggy.js';
import ZiggyVue from './ziggy-vue.js';
import { createI18n } from 'vue-i18n';

// Vuetify
import 'vuetify/styles';
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';

const vuetify = createVuetify({
    components,
    directives,
    theme: {
        defaultTheme: 'light'
    }
});

const appName = window.document.getElementsByTagName('title')[0]?.innerText;

// Получаем переводы из window (загруженные в app.blade.php)
const initialLocale = window.__LOCALE__ || 'ru';
const initialTranslations = window.__TRANSLATIONS__ || {};

// Создаем глобальный экземпляр i18n
const i18n = createI18n({
    legacy: false,
    locale: initialLocale,
    fallbackLocale: 'ru',
    messages: {
        [initialLocale]: initialTranslations
    },
    silentTranslationWarn: true,
    silentFallbackWarn: true,
    missingWarn: false,
    fallbackWarn: false
});

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        
        return app
            .use(plugin)
            .use(vuetify)
            .use(i18n)
            .use(ZiggyVue, Ziggy)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
