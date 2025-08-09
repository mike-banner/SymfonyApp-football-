import { defineConfig } from 'vite';

export default defineConfig({
  root: 'assets',
  build: {
    outDir: '../public/build',
    emptyOutDir: true,
    rollupOptions: {
      input: 'src/main.js'

    }
  }
});
