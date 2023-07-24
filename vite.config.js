import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from "@vitejs/plugin-vue";
import path from 'path'

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.js'],
      refresh: true,
    }),
    vue()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'src')
    }
  }
})

// // https://vitejs.dev/config/
// export default defineConfig({
//     plugins: [vue()],
//     resolve: {
//       alias: {
//         '@': path.resolve(__dirname, 'src')
//       }
//     }
//   })
