import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue2 from '@vitejs/plugin-vue2';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/assets/css/style.css',
                'resources/assets/js/main.js',
                'resources/assets/js/platoon.js',
                'resources/assets/js/division.js',
                'resources/assets/js/recruiting.js',
                'resources/assets/js/voice.js',
                'resources/assets/js/census-graph.js',
                'resources/assets/js/member-tags.js',
            ],
            refresh: true,
        }),
        vue2(),
    ],
    resolve: {
        alias: {
            'vue': 'vue/dist/vue.esm.js',
        },
    },
});
