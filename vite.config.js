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
                    // Разделяем Vuetify в отдельный чанк
                    if (id.includes('vuetify')) {
                        return 'vuetify';
                    }
                    
                    // Разделяем Vue и Inertia в отдельный чанк
                    if (id.includes('vue') || id.includes('@inertiajs')) {
                        return 'vue-core';
                    }
                    
                    // Разделяем другие большие библиотеки
                    if (id.includes('node_modules')) {
                        if (id.includes('pusher-js') || id.includes('laravel-echo')) {
                            return 'realtime';
                        }
                        if (id.includes('vuedraggable')) {
                            return 'drag-drop';
                        }
                        // Остальные node_modules библиотеки
                        return 'vendor';
                    }
                },
            },
        },
        // Увеличиваем лимит предупреждения до 1000 KB (1 MB)
        chunkSizeWarningLimit: 1000,
    },
});
