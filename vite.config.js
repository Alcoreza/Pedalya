import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/pages/landing.css',
                'resources/css/pages/login.css',
                'resources/css/pages/register.css',
                'resources/css/pages/manager-dashboard.css',
                'resources/css/pages/manager-bookings.css',
                'resources/css/pages/manager-schedule.css',
                'resources/js/app.js',
                'resources/js/pages/landing.js',
                'resources/js/pages/login.js',
                'resources/js/pages/register.js',
                'resources/js/pages/manager-dashboard.js',
                'resources/js/pages/manager-bookings.js',
                'resources/js/pages/manager-schedule.js',
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
