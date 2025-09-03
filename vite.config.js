import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [],
    root: '.',
    server: {
        host: '0.0.0.0',
        port: 3000,
        open: false,
        cors: {
            origin: true,
            credentials: true,
            methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            allowedHeaders: ['Content-Type', 'Authorization', 'X-Requested-With', 'Accept', 'Origin'],
        },
    },
    publicDir: 'public',
    build: {
        rollupOptions: {
            input: 'index.html',
            output: {
                manualChunks: undefined,
            },
        },
    },
});
