import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import * as glob from "glob";


export default defineConfig({
    plugins: [
        laravel({
            input: [
                ...glob.sync("resources/js/*.js"),
                ...glob.sync("resources/css/scss/*.scss")
            ],
            refresh: true,
        }),
    ],
});