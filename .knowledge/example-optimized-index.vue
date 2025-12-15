<!-- 
  优化示例：首页美化版本
  使用 Tailwind CSS + Animate.css
  
  对比：
  - 旧版本：src/pages/index/index.vue
  - 新版本：此文件展示如何优化
-->

<template>
  <view 
    class="relative overflow-hidden"
    :style="`height:${windowHeight}px`"
  >
    <!-- 背景渐变 -->
    <view class="absolute inset-0 gradient-primary opacity-90"></view>
    
    <!-- 装饰圆圈 -->
    <view class="absolute top-[-100rpx] right-[-100rpx] w-400 h-400 bg-white opacity-10 rounded-full"></view>
    <view class="absolute bottom-[-150rpx] left-[-100rpx] w-500 h-500 bg-white opacity-5 rounded-full"></view>
    
    <!-- 内容区域 -->
    <view class="relative z-10 flex flex-col justify-between h-full px-48 py-80">
      
      <!-- 顶部Logo和标题区域 -->
      <view class="animate__animated animate__fadeInDown">
        <!-- Logo -->
        <view class="flex justify-center mb-32">
          <view class="w-160 h-160 bg-white rounded-full flex justify-center items-center shadow-card">
            <text class="text-6xl">🔮</text>
          </view>
        </view>
        
        <!-- 主标题 -->
        <view class="text-center">
          <text class="text-5xl font-bold text-white block mb-16 animate__animated animate__fadeIn animate__delay-1s">
            解密2026
          </text>
          <text class="text-xl text-white opacity-90 block animate__animated animate__fadeIn animate__delay-2s">
            探索美好未来，解锁命运密码
          </text>
        </view>
      </view>
      
      <!-- 中间特色卡片（可选） -->
      <view class="animate__animated animate__zoomIn animate__delay-1s">
        <view class="glass-effect rounded-3xl p-32 mb-32">
          <!-- 功能亮点 -->
          <view class="flex justify-around">
            <view class="text-center">
              <text class="text-4xl block mb-8">✨</text>
              <text class="text-sm text-gray-700">精准测算</text>
            </view>
            <view class="text-center">
              <text class="text-4xl block mb-8">🌙</text>
              <text class="text-sm text-gray-700">命理解析</text>
            </view>
            <view class="text-center">
              <text class="text-4xl block mb-8">🎯</text>
              <text class="text-sm text-gray-700">运势预测</text>
            </view>
          </view>
        </view>
      </view>
      
      <!-- 底部按钮区域 -->
      <view class="animate__animated animate__fadeInUp animate__delay-2s">
        <!-- 主按钮 -->
        <view 
          class="bg-white rounded-full py-28 text-center shadow-card mb-24 transition-smooth active:scale-95"
          @click="navto"
        >
          <text class="text-primary text-2xl font-bold">探索 2026 🚀</text>
        </view>
        
        <!-- 副按钮（可选） -->
        <view 
          class="border-2 border-white rounded-full py-24 text-center transition-smooth active:opacity-70"
          @click="showIntro"
        >
          <text class="text-white text-lg">了解更多</text>
        </view>
        
        <!-- 底部提示 -->
        <view class="text-center mt-32">
          <text class="text-xs text-white opacity-80">
            已有 10,000+ 用户探索命运
          </text>
        </view>
      </view>
      
    </view>
    
    <!-- 粒子效果（可选） -->
    <view class="absolute inset-0 pointer-events-none">
      <view 
        v-for="i in 10" 
        :key="i"
        :class="[
          'absolute w-8 h-8 bg-white rounded-full opacity-20',
          `animate__animated animate__fadeInUp animate__infinite animate__slow`
        ]"
        :style="{
          left: `${Math.random() * 100}%`,
          top: `${Math.random() * 100}%`,
          animationDelay: `${i * 0.3}s`
        }"
      ></view>
    </view>
  </view>
</template>

<script>
export default {
  data() {
    return {
      title: 'Hello',
      windowHeight: ''
    }
  },
  onReady() {
    this.getsy()
  },
  onLoad(op) {
    console.log('----', op);
    if (op.sign) {
      uni.setStorageSync('app_parmas', op)
    }
    // 预加载省市数据
    this.preloadAreaData();
  },
  methods: {
    // 预加载省市数据
    async preloadAreaData() {
      try {
        console.log('[Index] 开始预加载省市数据');
        const cachedProvince = uni.getStorageSync('cache_provinceList');
        if (cachedProvince && cachedProvince.length > 0) {
          console.log('[Index] 省份数据已缓存，跳过');
          return;
        }
        const res = await this.$api.post('api/user/getProvinceList');
        if (res.code == 1) {
          uni.setStorageSync('cache_provinceList', res.data);
          console.log('[Index] 省份数据预加载完成');
        }
      } catch (e) {
        console.error('[Index] 预加载失败', e);
      }
    },
    
    getsy() {
      uni.getSystemInfo({
        success: (res) => {
          this.windowHeight = res.windowHeight
        }
      })
    },
    
    navto() {
      // 添加触感反馈
      uni.vibrateShort();
      
      uni.navigateTo({
        url: '/pages/index/gossip',
        animationType: 'zoom-fade-out',
        animationDuration: 300
      })
    },
    
    showIntro() {
      uni.showModal({
        title: '关于解密2026',
        content: '通过四柱八字测算，帮你探索2026年的运势走向',
        confirmText: '开始探索',
        success: (res) => {
          if (res.confirm) {
            this.navto()
          }
        }
      })
    }
  }
}
</script>

<style scoped>
/* 自定义样式（Tailwind未覆盖的） */

/* 脉动动画 */
@keyframes pulse-slow {
  0%, 100% {
    opacity: 0.2;
    transform: scale(1);
  }
  50% {
    opacity: 0.4;
    transform: scale(1.05);
  }
}

.pulse-slow {
  animation: pulse-slow 3s ease-in-out infinite;
}

/* 浮动动画 */
@keyframes float {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-20rpx);
  }
}

.float {
  animation: float 3s ease-in-out infinite;
}
</style>

<!-- 
  使用说明：
  
  1. 复制此文件内容到 src/pages/index/index.vue
  2. 保存并运行 npm run dev:h5
  3. 查看效果
  
  主要改进：
  - ✅ 使用 Tailwind CSS 工具类（flex、p-32、rounded-full等）
  - ✅ 添加 Animate.css 动画（fadeInDown、fadeInUp、zoomIn）
  - ✅ 添加渐变背景（gradient-primary）
  - ✅ 添加毛玻璃效果（glass-effect）
  - ✅ 添加交互反馈（active:scale-95、active:opacity-70）
  - ✅ 优化视觉层次和间距
  - ✅ 添加装饰元素（圆圈、粒子）
-->
