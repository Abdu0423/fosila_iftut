import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { Ziggy } from './ziggy.js';
import ZiggyVue from './ziggy-vue.js';
import { initLocale, getCurrentLocale } from './utils/i18n';

// Инициализируем язык при загрузке приложения
initLocale();

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

// Устанавливаем заголовок X-Locale для всех Inertia запросов
if (typeof window !== 'undefined' && window.axios) {
    const currentLocale = getCurrentLocale();
    window.axios.defaults.headers.common['X-Locale'] = currentLocale;
}

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(vuetify)
            .use(ZiggyVue, Ziggy)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
