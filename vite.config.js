import { defineConfig } from 'vite'
import { fileViewerRenderers } from '@file-viewer/vite-plugin'

/**
 * @file-viewer on-demand: PDF + Word + Excel only
 * @see https://github.com/flyfish-dev/file-viewer
 */
export default defineConfig({
  plugins: [
    fileViewerRenderers({
      copyAssets: true,
      // Avoid circular chunk TDZ in production builds.
      chunkStrategy: 'none',
      formats: ['pdf', 'docx', 'xlsx'],
    }),
  ],
  publicDir: false,
  build: {
    outDir: 'public/build/file-viewer',
    emptyOutDir: true,
    manifest: true,
    chunkSizeWarningLimit: 5000,
    rollupOptions: {
      input: 'frontend/file-viewer/main.js',
      output: {
        entryFileNames: 'assets/[name]-[hash].js',
        chunkFileNames: 'assets/[name]-[hash].js',
        assetFileNames: 'assets/[name]-[hash][extname]',
        manualChunks(id) {
          if (!id.includes('node_modules')) {
            return undefined
          }
          if (id.includes('@file-viewer/') || id.includes('pdfjs-dist') || id.includes('e-virt-table')) {
            return 'file-viewer-bundle'
          }
          return undefined
        },
      },
    },
  },
  base: '/build/file-viewer/',
})
