import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import livewire from "@defstudio/vite-livewire-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: false, // disable default blade refresh
        }),
        livewire({
            refresh: ["resources/css/app.css"], // enable Tailwind refresh
        }),
    ],
});
