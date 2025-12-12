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
                    // НЕ разделяем Vuetify - оставляем его в основном бандле
                    // Это предотвращает проблемы с циклическими зависимостями
                    
                    // Разделяем только другие библиотеки
                    if (id.includes('node_modules')) {
                        // Vue отдельно (но не Vuetify)
                        if (id.includes('vue') && !id.includes('vuetify') && !id.includes('vuedraggable')) {
                            return 'vue-runtime';
                        }
                        
                        // Inertia отдельно
                        if (id.includes('@inertiajs')) {
                            return 'inertia';
                        }
                        
                        // Ziggy отдельно
                        if (id.includes('ziggy') || id.includes('ziggy.js')) {
                            return 'ziggy';
                        }
                        
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
                        
                        // Остальные vendor библиотеки (но НЕ Vuetify)
                        if (!id.includes('vuetify')) {
                            return 'vendor';
                        }
                    }
                    // Vuetify остается в основном бандле
                },
            },
        },
        // Увеличиваем лимит предупреждения до 2500 KB (2.5 MB)
        // Vuetify будет в основном бандле, это нормально
        chunkSizeWarningLimit: 2500,
        // Включаем source maps только для отладки (можно отключить в production)
        sourcemap: false,
        // Используем esbuild для минификации (более стабильно, чем terser)
        minify: 'esbuild',
    },
});
