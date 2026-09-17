import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// En Docker el backend vive en otro contenedor, por eso el destino del proxy es configurable.
const apiTarget = process.env.VITE_API_PROXY_TARGET || 'http://localhost:8000'

export default defineConfig({
  plugins: [react()],
  server: {
    host: true,
    port: 5173,
    // Proxy de /api para que el navegador hable siempre con el mismo origen.
    proxy: {
      '/api': {
        target: apiTarget,
        changeOrigin: true,
      },
    },
  },
})
