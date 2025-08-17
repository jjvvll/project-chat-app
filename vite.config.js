import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],

      preview: {
    port: process.env.PORT || 3000,
    host: '0.0.0.0'
  },
  server: {
    port: process.env.PORT || 3000,
    host: '0.0.0.0'
  }
});
