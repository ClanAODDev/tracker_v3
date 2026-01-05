import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/assets/scss/main.scss',
                'resources/assets/js/libs-bundle.js',
                'resources/assets/js/main.js',
                'resources/assets/js/platoon.js',
                'resources/assets/js/division.js',
                'resources/assets/js/recruiting.js',
                'resources/assets/js/voice.js',
                'resources/assets/js/census-graph.js',
                'resources/assets/js/member-tags.js',
                'resources/assets/js/retention-graph.js',
                'resources/assets/js/tickets.js',
                'resources/assets/js/training.js',
            ],
            refresh: true,
        }),
        vue(),
    ],
    build: {
        rollupOptions: {
            output: {
                globals: {
                    jquery: 'jQuery'
                }
            }
        }
    },
    resolve: {
        alias: {
            jquery: '/resources/assets/js/jquery-global.js'
        }
    }
});
