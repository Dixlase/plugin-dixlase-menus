import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'plugins/DixlaseMenus/resources/src/js/app.js',
                'plugins/DixlaseMenus/resources/src/admin/js/app.js',
            ],
            refresh: true,
        }),
    ],
    build: {
        outDir: 'plugins/DixlaseMenus/resources/assets',
    },
});
