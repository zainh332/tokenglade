import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from "@vitejs/plugin-vue";
import path from 'path'
import { createRequire } from 'module';

const require = createRequire(import.meta.url);

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'src/main.js',
    ],
      refresh: true,
    }),
    vue()],
    resolve: {
      alias: {
        '@': path.resolve(__dirname, 'src'),
      }
    }
})
