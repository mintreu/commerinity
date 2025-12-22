import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            //,,'resources/css/filament/admin/theme.css'
            input: ['resources/css/app.css','resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
