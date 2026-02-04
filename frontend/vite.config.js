import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  server: {
    port: 5173,
    open: true
  },
  build: {
    sourcemap: false,  // Ẩn source code khi deploy production
    outDir: 'dist'     // Output directory
  }
})
