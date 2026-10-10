import { fileURLToPath, URL } from 'node:url';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

function exposeRouteHelper() {
    return {
        name: 'expose-route-helper',
        enforce: 'pre',
        transform(code, id) {
            if (!id.endsWith('.vue') || !code.includes('route(') || code.includes('const route = window.route')) {
                return null;
            }

            return code.replace(/<script([^>]*)>/, (match, attrs) => {
                if (attrs.includes('src=')) {
                    return match;
                }

                return `<script${attrs}>\nconst route = window.route\n`;
            });
        },
    };
}

export default defineConfig({
    plugins: [
        exposeRouteHelper(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
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
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
    server: {
        hmr: {
            host: 'localhost',
        },
    },
    optimizeDeps: {
        include: ['vue', 'vue-router', 'pinia', 'vuetify'],
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks: (id) => {
                    if (id.includes('node_modules')) {
                        if (id.includes('vue-router') || (id.includes('vue') && !id.includes('vuetify') && !id.includes('vuedraggable'))) {
                            return 'vue-runtime';
                        }

                        if (id.includes('@mdi/font')) {
                            return 'mdi-icons';
                        }

                        if (id.includes('pusher-js') || id.includes('laravel-echo')) {
                            return 'realtime';
                        }

                        if (id.includes('vuedraggable')) {
                            return 'drag-drop';
                        }

                        if (id.includes('axios')) {
                            return 'axios';
                        }

                        if (!id.includes('vuetify')) {
                            return 'vendor';
                        }
                    }
                },
            },
        },
        chunkSizeWarningLimit: 2500,
        sourcemap: false,
        minify: 'esbuild',
    },
});
