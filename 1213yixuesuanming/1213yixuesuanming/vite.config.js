import { defineConfig } from 'vite'
import uni from '@dcloudio/vite-plugin-uni'

export default defineConfig({
  plugins: [uni()],
  build: {
    sourcemap: false,  // 禁用sourcemap避免构建冲突
    rollupOptions: {
      output: {
        manualChunks: undefined,
      }
    }
  },
  // 开发服务器配置 - 解决CORS跨域问题
  server: {
    proxy: {
      '/api': {
        target: 'https://yixueadmin.linqingkeji.com',
        changeOrigin: true,
        rewrite: (path) => path
      }
    }
  }
})
