import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from "@vitejs/plugin-vue";
import path from 'path'

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'resources/js/app.js',
    ],
      refresh: true,
    }),
    vue()],
    resolve: {
      alias: {
        '@': path.resolve(__dirname, 'src'),
        // 'vee-validate': 'vee-validate/dist/vee-validate.esm.js',
      }
    },
    
    build: {
      outDir: 'dist',
      rollupOptions: {
        input: ['src/main.js']
      }
    }
})
