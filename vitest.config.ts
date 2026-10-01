import react from '@vitejs/plugin-react';
import { defineConfig } from 'vitest/config';

export default defineConfig({
    plugins: [react()],
    test: {
        environment: 'jsdom',
        globals: true,
        setupFiles: ['./resources/js/test/setup.ts'],
        include: ['resources/js/**/*.{test,spec}.{ts,tsx}'],
        coverage: {
            provider: 'v8',
            reportsDirectory: './coverage-frontend',
            include: ['resources/js/**/*.{ts,tsx}'],
            exclude: [
                'resources/js/test/**',
                'resources/js/types/**',
                'resources/js/**/*.d.ts',
            ],
        },
    },
    resolve: {
        alias: {

            '@': new URL('./resources/js', import.meta.url).pathname,
        },
    },
});
