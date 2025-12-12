/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
import { Ziggy } from './ziggy.js';
import { getCurrentLocale } from './utils/i18n';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Устанавливаем заголовок X-Locale для всех axios запросов
window.axios.defaults.headers.common['X-Locale'] = getCurrentLocale();

// Обновляем заголовок при изменении языка в localStorage
if (typeof window !== 'undefined') {
  // Слушаем изменения localStorage (через событие storage)
  window.addEventListener('storage', (e) => {
    if (e.key === 'locale' && e.newValue) {
      window.axios.defaults.headers.common['X-Locale'] = e.newValue;
    }
  });
  
  // Также обновляем при прямом изменении через setItem
  const originalSetItem = Storage.prototype.setItem;
  Storage.prototype.setItem = function(key, value) {
    originalSetItem.apply(this, arguments);
    if (key === 'locale' && window.axios) {
      window.axios.defaults.headers.common['X-Locale'] = value;
    }
  };
}

// Добавляем функцию route в глобальную область
window.route = (name, params, absolute, config = Ziggy) => {
    if (typeof name === 'undefined') {
        return config;
    }
    
    if (typeof name === 'object') {
        params = name;
        name = undefined;
    }
    
    if (typeof params === 'boolean') {
        absolute = params;
        params = undefined;
    }
    
    if (typeof params === 'object' && params !== null) {
        params = Object.keys(params).reduce((result, key) => {
            if (params[key] !== null && params[key] !== undefined) {
                result[key] = params[key];
            }
            return result;
        }, {});
    }
    
    const url = new URL(config.url);
    
    if (name) {
        const route = config.routes[name];
        if (!route) {
            throw new Error(`Route [${name}] not found.`);
        }
        
        let uri = route.uri;
        
        if (params) {
            Object.keys(params).forEach(key => {
                uri = uri.replace(`{${key}}`, params[key]);
            });
        }
        
        if (route.parameters) {
            route.parameters.forEach(param => {
                if (!params || !params[param]) {
                    throw new Error(`Missing required parameter [${param}] for route [${name}].`);
                }
            });
        }
        
        url.pathname = uri;
    }
    
    return absolute ? url.href : url.pathname + url.search;
};

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
    wsHost: import.meta.env.VITE_PUSHER_HOST ? import.meta.env.VITE_PUSHER_HOST : `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
    wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
    wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        },
    },
});
