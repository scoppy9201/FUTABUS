import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import path from 'path';
import { globSync } from 'glob';
import { fileURLToPath } from 'url';

const rootDir = path.dirname(fileURLToPath(import.meta.url));

// Thu thập app.css + app.js của toàn bộ package (chuẩn Mindigo modular)
const packageAssets = globSync('packages/*/*/src/resources/**/app.{css,js}', {
    cwd: rootDir,
}).map((file) => path.resolve(rootDir, file));

const inputs = [
    'resources/css/app.css',
    'resources/js/app.js',
    ...packageAssets,
];

export default defineConfig({
    plugins: [
        laravel({
            input: inputs,
            refresh: true,
        }),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': path.resolve(rootDir, './resources/js'),
        }
    },
    server: {
        host: '127.0.0.1',
        watch: {
            // Windows bind mounts may not forward file events into Docker.
            usePolling: process.env.VITE_USE_POLLING === 'true',
            interval: 500,
            ignored: ['**/vendor/**', '**/storage/**', '**/public/build/**'],
        },
        hmr: {
            host: '127.0.0.1',
            overlay: false,
        },
    },
    build: {
        sourcemap: true,
        emptyOutDir: true,
    }
});
