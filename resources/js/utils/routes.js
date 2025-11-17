/**
 * Хелпер для гарантированной генерации абсолютных URL
 * Решает проблему дублирования путей в Inertia + Ziggy
 */

/**
 * Генерирует абсолютный URL для маршрута
 * @param {string} name - Имя маршрута
 * @param {*} params - Параметры маршрута
 * @returns {string} Абсолютный URL (начинается с /)
 */
export function absoluteRoute(name, params = undefined) {
    // Получаем путь от Ziggy
    let path = route(name, params);
    
    // Если путь не начинается с /, добавляем его
    if (!path.startsWith('/')) {
        path = '/' + path;
    }
    
    return path;
}

/**
 * Альтернатива - явное построение URL
 */
export const routes = {
    // Admin routes
    admin: {
        users: {
            index: () => '/admin/users',
            create: () => '/admin/users/create',
            store: () => '/admin/users',
            show: (id) => `/admin/users/${id}`,
            edit: (id) => `/admin/users/${id}/edit`,
            update: (id) => `/admin/users/${id}`,
            destroy: (id) => `/admin/users/${id}`,
        },
        subjects: {
            index: () => '/admin/subjects',
            create: () => '/admin/subjects/create',
            store: () => '/admin/subjects',
            show: (id) => `/admin/subjects/${id}`,
            edit: (id) => `/admin/subjects/${id}/edit`,
            update: (id) => `/admin/subjects/${id}`,
        },
        schedules: {
            index: () => '/admin/schedules',
            create: () => '/admin/schedules/create',
            store: () => '/admin/schedules',
            show: (id) => `/admin/schedules/${id}`,
            edit: (id) => `/admin/schedules/${id}/edit`,
            update: (id) => `/admin/schedules/${id}`,
        },
        lessons: {
            index: () => '/admin/lessons',
            create: () => '/admin/lessons/create',
            store: () => '/admin/lessons',
            show: (id) => `/admin/lessons/${id}`,
            edit: (id) => `/admin/lessons/${id}/edit`,
            update: (id) => `/admin/lessons/${id}`,
        },
        tests: {
            index: () => '/admin/tests',
            create: () => '/admin/tests/create',
            store: () => '/admin/tests',
            show: (id) => `/admin/tests/${id}`,
            edit: (id) => `/admin/tests/${id}/edit`,
            update: (id) => `/admin/tests/${id}`,
        },
        syllabuses: {
            index: () => '/admin/syllabuses',
            create: () => '/admin/syllabuses/create',
            store: () => '/admin/syllabuses',
            show: (id) => `/admin/syllabuses/${id}`,
            edit: (id) => `/admin/syllabuses/${id}/edit`,
            update: (id) => `/admin/syllabuses/${id}`,
        },
    },
    
    // Teacher routes
    teacher: {
        // Добавьте по необходимости
    },
    
    // Student routes
    student: {
        // Добавьте по необходимости
    }
};

export default { absoluteRoute, routes };

