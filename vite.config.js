import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'

export default defineConfig({
  plugins: [
    laravel({
      // match what you actually import in Blade
      input: ['resources/css/app.css', 'resources/js/app.js'],
      refresh: true,
    }),
  ],
  build: {
    outDir: 'public/build',
    manifest: 'manifest.json',
    // IMPORTANT: ensure no custom manifestDir is set
    // (remove it if present). We want: public/build/manifest.json
  },
})
