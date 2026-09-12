import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from "@vitejs/plugin-vue"
import path from 'path'

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'src/main.js',
        'src/admin-main.js',
      ],
      refresh: true,
    }),
    vue()
  ],
  server: {
    host: '127.0.0.1',
    port: 5173,
    strictPort: true,
    cors: true,
    headers: {
      'Access-Control-Allow-Origin': '*',
    },
    hmr: {
      host: '127.0.0.1',
    },
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'src'),
    }
  }
});
