import { fileURLToPath, URL } from 'node:url';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';

const jsPackage = (name) => fileURLToPath(new URL(`./resources/js/packages/${name}`, import.meta.url));

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
        vue(),
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
            '@menu': jsPackage('ts-menu'),
            '@admin': jsPackage('vue-admin'),
            '@user': jsPackage('vue-user'),
            '@address': jsPackage('vue-address'),
            '@currency': jsPackage('vue-currency'),
            '@customer': jsPackage('vue-customer'),
            '@language': jsPackage('vue-language'),
            '@media': jsPackage('vue-media'),
            '@order': jsPackage('vue-order'),
            '@product': jsPackage('vue-product'),
            '@setting': jsPackage('vue-setting'),
            '@stock': jsPackage('vue-stock'),
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
