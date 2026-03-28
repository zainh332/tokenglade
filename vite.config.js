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
      ],
      refresh: true,
    }),
    vue()
  ],

  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'src'),
    }
  },

  server: {
    host: '127.0.0.1', // 🔥 force IPv4
    port: 5173,
    strictPort: true,

    cors: {
      origin: ['http://tokenglade.test'],
    },

    hmr: {
      host: 'tokenglade.test',
    }
  }
})