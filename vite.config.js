import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/pages/requests.css',
                'resources/css/pages/registrations.css',
                'resources/css/pages/patients.css',
                'resources/css/pages/reviews.css',
                'resources/css/pages/admin.css',
                'resources/js/pages/requests.js',
                'resources/js/pages/reviews.js',
                'resources/js/pages/admin.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
