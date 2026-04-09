import { defineConfig } from 'vite';
import path from 'path';

export default defineConfig({
    build: {
        outDir: path.resolve(__dirname, 'resources/assets'),
        emptyOutDir: false,
        copyPublicDir: false,
        manifest: 'manifest.json',
        rollupOptions: {
            input: {
                app: path.resolve(__dirname, 'resources/src/js/app.js'),
                'admin-app': path.resolve(__dirname, 'resources/src/admin/js/app.js'),
            },
            output: {
                entryFileNames: (chunkInfo) => {
                    if (chunkInfo.facadeModuleId?.includes('/admin/')) {
                        return 'admin/js/[name].js';
                    }
                    return 'js/[name].js';
                },
                chunkFileNames: 'js/[name].js',
                assetFileNames: 'css/[name][extname]',
            },
        },
    },
});
