import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'plugins/DixlaseMenu/resources/src/js/app.js',
                'plugins/DixlaseMenu/resources/src/css/style.css',
            ],
            refresh: true,
        }),
    ],
    build: {
        outDir: 'plugins/DixlaseMenu/resources/assets',
    },
});