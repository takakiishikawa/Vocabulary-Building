import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import react from "@vitejs/plugin-react";
import sass from "vite-plugin-sass";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/js/App.tsx"],
            refresh: true,
        }),
        react(),
        sass(),
    ],
    esbuild: {
        loader: "tsx",
    },
    css: {
        modules: {
            localsConvention: "camelCase",
        },
        preprocessorOptions: {
            scss: {},
        },
    },
});
