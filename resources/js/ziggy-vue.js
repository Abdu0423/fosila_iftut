import { Ziggy } from './ziggy.js';

// Создаем функцию route для window
function createRoute(name, params, absolute, config = Ziggy) {
    if (typeof name === 'undefined') {
        return config.url || '/';
    }
    
    const routeConfig = config.routes?.[name];
    if (!routeConfig) {
        // Если route не найден, возвращаем имя как путь (fallback)
        return '/' + name.replace(/\./g, '/');
    }
    
    let url = routeConfig.uri || '';
    
    // Заменяем параметры в URL
    if (params && typeof params === 'object') {
        Object.keys(params).forEach(key => {
            url = url.replace(`{${key}}`, params[key]);
            url = url.replace(`{${key}?}`, params[key] || '');
        });
    }
    
    // Удаляем оставшиеся неиспользованные параметры
    url = url.replace(/\{[^}]+\?\}/g, '');
    url = url.replace(/\{[^}]+\}/g, '');
    
    // Убираем двойные слеши
    url = url.replace(/\/+/g, '/');
    
    // Убираем trailing slash если не корень
    if (url.length > 1 && url.endsWith('/')) {
        url = url.slice(0, -1);
    }
    
    // Добавляем leading slash
    if (!url.startsWith('/')) {
        url = '/' + url;
    }
    
    // Добавляем базовый URL если absolute
    if (absolute && config.url) {
        url = config.url + url;
    }
    
    return url;
}

// Добавляем в window для глобального доступа
if (typeof window !== 'undefined') {
    window.route = createRoute;
    window.Ziggy = Ziggy;
}

export default {
    install: (app) => {
        app.config.globalProperties.$route = createRoute;
        app.config.globalProperties.$ziggy = Ziggy;
        app.provide('ziggy', Ziggy);
        app.provide('route', createRoute);
        app.config.globalProperties.route = createRoute;
    }
};
