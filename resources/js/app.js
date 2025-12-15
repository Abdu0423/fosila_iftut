import './bootstrap';
import '../css/app.css';

import { createApp, h, watch } from 'vue';
import { createInertiaApp, usePage } from '@inertiajs/vue3';
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

// Получаем начальные переводы из window (загруженные в app.blade.php)
const initialLocale = window.__LOCALE__ || 'ru';
const initialTranslations = window.__TRANSLATIONS__ || {};

console.log('🌐 App.js: Initial locale:', initialLocale);
console.log('🌐 App.js: Initial translations keys:', Object.keys(initialTranslations));

// Создаем глобальный экземпляр i18n
const i18n = createI18n({
    legacy: false,
    locale: initialLocale,
    fallbackLocale: 'ru',
    messages: {
        ru: initialLocale === 'ru' ? initialTranslations : {},
        tg: initialLocale === 'tg' ? initialTranslations : {}
    },
    silentTranslationWarn: true,
    silentFallbackWarn: true,
    missingWarn: false,
    fallbackWarn: false
});

// Функция для обновления переводов
function updateI18n(locale, translations) {
    if (!locale) return;
    
    console.log('🌐 Updating i18n:', locale, 'translations keys:', Object.keys(translations || {}));
    
    // Устанавливаем локаль
    i18n.global.locale.value = locale;
    
    // Загружаем переводы
    if (translations && typeof translations === 'object' && Object.keys(translations).length > 0) {
        i18n.global.setLocaleMessage(locale, translations);
    }
}

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        // Получаем переводы из начальной страницы
        const pageLocale = props.initialPage?.props?.locale || initialLocale;
        const pageTranslations = props.initialPage?.props?.translations || initialTranslations;
        
        console.log('🌐 Setup: Page locale:', pageLocale);
        console.log('🌐 Setup: Page translations:', Object.keys(pageTranslations));
        
        // Обновляем i18n с переводами из страницы
        updateI18n(pageLocale, pageTranslations);
        
        const app = createApp({ render: () => h(App, props) });
        
        // Создаем mixin для обновления переводов при навигации
        app.mixin({
            created() {
                // Для Composition API компонентов
                if (this.$page) {
                    const checkAndUpdate = () => {
                        const loc = this.$page.props?.locale;
                        const trans = this.$page.props?.translations;
                        if (loc && trans && Object.keys(trans).length > 0) {
                            updateI18n(loc, trans);
                        }
                    };
                    checkAndUpdate();
                }
            },
            watch: {
                '$page.props.locale': {
                    handler(newLocale) {
                        if (newLocale && this.$page?.props?.translations) {
                            updateI18n(newLocale, this.$page.props.translations);
                        }
                    },
                    immediate: true
                },
                '$page.props.translations': {
                    handler(newTranslations) {
                        if (newTranslations && this.$page?.props?.locale) {
                            updateI18n(this.$page.props.locale, newTranslations);
                        }
                    },
                    immediate: true
                }
            }
        });
        
        // Слушаем события Inertia для обновления переводов
        document.addEventListener('inertia:success', (event) => {
            const page = event.detail?.page;
            if (page?.props?.locale && page?.props?.translations) {
                updateI18n(page.props.locale, page.props.translations);
            }
        });
        
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
