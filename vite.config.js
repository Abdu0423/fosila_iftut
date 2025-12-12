import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
    server: {
        hmr: {
            host: 'localhost',
        },
    },
    optimizeDeps: {
        include: ['vue', '@inertiajs/vue3', 'vuetify'],
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks: (id) => {
                    // Vuetify должен быть в одном чанке (избегаем проблем с инициализацией)
                    if (id.includes('vuetify')) {
                        return 'vuetify';
                    }
                    
                    // Разделяем Vue отдельно
                    if (id.includes('vue') && !id.includes('vuetify') && !id.includes('vuedraggable')) {
                        return 'vue-runtime';
                    }
                    
                    // Inertia отдельно
                    if (id.includes('@inertiajs')) {
                        return 'inertia';
                    }
                    
                    // Ziggy отдельно (может быть большим)
                    if (id.includes('ziggy') || id.includes('ziggy.js')) {
                        return 'ziggy';
                    }
                    
                    // Разделяем другие большие библиотеки
                    if (id.includes('node_modules')) {
                        // Иконки MDI
                        if (id.includes('@mdi/font')) {
                            return 'mdi-icons';
                        }
                        // Real-time библиотеки
                        if (id.includes('pusher-js') || id.includes('laravel-echo')) {
                            return 'realtime';
                        }
                        // Drag and drop
                        if (id.includes('vuedraggable')) {
                            return 'drag-drop';
                        }
                        // Axios
                        if (id.includes('axios')) {
                            return 'axios';
                        }
                        // Остальные vendor библиотеки
                        return 'vendor';
                    }
                },
            },
        },
        // Увеличиваем лимит предупреждения до 2000 KB (2 MB)
        // Vuetify - большая библиотека, это нормально
        chunkSizeWarningLimit: 2000,
        // Включаем source maps только для отладки (можно отключить в production)
        sourcemap: false,
    },
});
