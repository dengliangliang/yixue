import { defineConfig } from 'vite'
import uni from '@dcloudio/vite-plugin-uni'

export default defineConfig({
  plugins: [uni()],
  build: {
    sourcemap: false  // 禁用sourcemap避免构建冲突
  }
})
