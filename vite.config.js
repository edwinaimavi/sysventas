import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',

                'resources/js/pages/roles.js',
                'resources/js/pages/user.js',
                'resources/js/pages/branch.js',
                'resources/js/pages/client.js',
                'resources/js/pages/guarantor.js',
                'resources/js/pages/loan-payment.js',
                'resources/js/pages/loan.js',
                'resources/js/pages/report.js',
                'resources/js/pages/reminder.js',
            ],
            refresh: true,
        }),
    ],
});
