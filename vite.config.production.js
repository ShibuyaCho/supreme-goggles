import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
    build: {
        outDir: 'public/dist',
        emptyOutDir: true,
        rollupOptions: {
            input: {
                app: resolve(__dirname, 'resources/js/app.js'),
                styles: resolve(__dirname, 'resources/css/app.css')
            },
            output: {
                entryFileNames: 'js/[name].[hash].js',
                chunkFileNames: 'js/[name].[hash].js',
                assetFileNames: (assetInfo) => {
                    const info = assetInfo.name.split('.');
                    const extType = info[info.length - 1];
                    if (/\.(css)$/.test(assetInfo.name)) {
                        return `css/[name].[hash].${extType}`;
                    }
                    return `assets/[name].[hash].${extType}`;
                }
            }
        },
        minify: 'esbuild',
        sourcemap: false, // Disable for production
        cssCodeSplit: true,
        target: 'esnext'
    },
    define: {
        'process.env.NODE_ENV': JSON.stringify('production')
    },
    server: {
        https: true,
        host: '0.0.0.0',
        port: 5173,
        strictPort: true
    },
    preview: {
        https: true,
        host: '0.0.0.0',
        port: 4173,
        strictPort: true
    }
});
