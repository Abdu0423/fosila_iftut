import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { Ziggy } from './ziggy.js';
import ZiggyVue from './ziggy-vue.js';
import { createI18n } from 'vue-i18n';
import { createI18nUpdater } from './plugins/i18n-updater.js';

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

// Создаем глобальный экземпляр i18n
const i18n = createI18n({
    legacy: false,
    locale: 'ru',
    fallbackLocale: 'ru',
    messages: {},
    silentTranslationWarn: true,
    silentFallbackWarn: true
});

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        
        // Инициализируем i18n при первой загрузке
        const initialLocale = props.initialPage.props.locale || 'ru';
        const initialTranslations = props.initialPage.props.translations || {};
        i18n.global.locale.value = initialLocale;
        i18n.global.setLocaleMessage(initialLocale, initialTranslations);
        
        // Обновляем i18n при каждой навигации Inertia
        const updateI18nFromPage = (page) => {
            const locale = page?.props?.locale || 'ru';
            const translations = page?.props?.translations || {};
            
            if (locale !== i18n.global.locale.value) {
                i18n.global.locale.value = locale;
            }
            i18n.global.setLocaleMessage(locale, translations);
        };
        
        // Подписываемся на события Inertia
        if (typeof window !== 'undefined') {
            window.addEventListener('inertia:success', (event) => {
                if (event.detail?.page) {
                    updateI18nFromPage(event.detail.page);
                }
            });
        }
        
        return app
            .use(plugin)
            .use(vuetify)
            .use(i18n)
            .use(createI18nUpdater(i18n))
            .use(ZiggyVue, Ziggy)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
