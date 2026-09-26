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
                // Content-hash JS/CSS entry filenames so each build
                // that changes bytes gets a new URL — automatic cache
                // busting for browsers that already cached the prior
                // asset. Identical builds still hash to the same
                // filename (Vite's `[hash]` is a content hash, not a
                // build timestamp), so no-op rebuilds do not churn
                // URLs. Fonts / images stay stable to keep the browser
                // font cache warm across rebuilds.
                //
                // See dixlase Sep 2026 incident: pre-hash stable URLs
                // let iOS Safari cache pre-refactor CSS indefinitely,
                // and "Clear History and Website Data" did not help.
                entryFileNames: (chunkInfo) => {
                    if (chunkInfo.facadeModuleId?.includes('/admin/')) {
                        return 'admin/js/[name]-[hash].js';
                    }
                    return 'js/[name]-[hash].js';
                },
                chunkFileNames: 'js/[name]-[hash].js',
                assetFileNames: 'css/[name]-[hash][extname]',
            },
        },
    },
    css: {
        // Anchor PostCSS to this repo so Vite does not walk up and
        // pick up the Dixlase Core `postcss.config.js` (which requires
        // Tailwind — absent from the plugin ZIP and unavailable when
        // building against the release core). Empty object = no
        // PostCSS plugins for this plugin's build.
        postcss: {},
    },
});
