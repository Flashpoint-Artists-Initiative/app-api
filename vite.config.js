import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            hotFile: 'public/hot',
            buildDirectory: 'build',
            input: ['resources/js/app.js'],
            refresh: true,
        }),
    ],
});
