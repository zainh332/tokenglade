import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from "@vitejs/plugin-vue";
import path from 'path'

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        // 'resources/js/app.js',
        'src/main.js',
    ],
      refresh: true,
    }),
    vue()],
    resolve: {
      alias: {
        '@': path.resolve(__dirname, 'src'),
      }
    },
    
    build: {
      outDir: 'dist',
      rollupOptions: {
        // input: ['src/main.js']
      }
    }
})
