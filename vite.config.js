import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import viteCompression from 'vite-plugin-compression';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.js'],
            refresh: true,
        }),
        viteCompression()
    ],
    optimizeCss: true,
    resolve: {
        alias: {
            '~mdb-ui-kit': '/node_modules/mdb-ui-kit',
            '~mdb-ecommerce-gallery': '/node_modules/mdb-ecommerce-gallery',
            '~mdb-wysiwyg-editor': '/node_modules/mdb-wysiwyg-editor',
            '@fortawesome': '/node_modules/@fortawesome'
        }
    }
});
