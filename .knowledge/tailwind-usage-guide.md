# Tailwind CSS + Animate.css 使用指南

## 🎨 已安装组件

- ✅ Tailwind CSS 3.x
- ✅ Animate.css 4.x
- ✅ 保留 uView-plus（现有组件库）

---

## 📚 快速上手示例

### 1️⃣ 基础布局和间距

```vue
<template>
  <!-- 旧写法 -->
  <view style="padding: 20rpx; margin: 10rpx;">
    <text>旧样式</text>
  </view>
  
  <!-- 新写法：使用Tailwind -->
  <view class="p-20 m-10 bg-white rounded-lg shadow-soft">
    <text class="text-lg text-gray-800">新样式</text>
  </view>
</template>
```

**常用间距类：**
- `p-4` = padding: 8rpx
- `m-8` = margin: 16rpx
- `px-12` = padding-left/right: 24rpx
- `mt-16` = margin-top: 32rpx

---

### 2️⃣ 颜色和背景

```vue
<template>
  <!-- 主题色按钮 -->
  <view class="bg-primary text-white px-32 py-16 rounded-full text-center">
    解密2026
  </view>
  
  <!-- 渐变背景 -->
  <view class="gradient-primary h-200 rounded-xl">
    <text class="text-white text-2xl">渐变卡片</text>
  </view>
  
  <!-- 毛玻璃效果 -->
  <view class="glass-effect p-24 rounded-lg">
    <text>毛玻璃效果</text>
  </view>
</template>
```

**颜色变量：**
- `bg-primary` - 主题金色 #E2C289
- `bg-white` - 白色
- `text-gray-800` - 深灰文字
- `text-primary` - 主题色文字

---

### 3️⃣ Flexbox 布局

```vue
<template>
  <!-- 水平居中 -->
  <view class="flex justify-center items-center h-200">
    <text>水平垂直居中</text>
  </view>
  
  <!-- 左右分布 -->
  <view class="flex justify-between items-center px-24 py-16">
    <text>左侧</text>
    <text>右侧</text>
  </view>
  
  <!-- 垂直排列 -->
  <view class="flex flex-col gap-12">
    <view class="bg-white p-12 rounded">项目1</view>
    <view class="bg-white p-12 rounded">项目2</view>
    <view class="bg-white p-12 rounded">项目3</view>
  </view>
</template>
```

---

### 4️⃣ Animate.css 动画

```vue
<template>
  <!-- 淡入动画 -->
  <view class="animate__animated animate__fadeIn">
    <text>淡入效果</text>
  </view>
  
  <!-- 从下往上 -->
  <view class="animate__animated animate__fadeInUp animate__delay-1s">
    <text>延迟1秒后从下往上</text>
  </view>
  
  <!-- 缩放动画 -->
  <view class="animate__animated animate__zoomIn animate__faster">
    <text>快速缩放</text>
  </view>
  
  <!-- 弹跳进入 -->
  <view class="animate__animated animate__bounceIn">
    <text>弹跳进入</text>
  </view>
</template>

<script setup>
import { ref } from 'vue'

// 动态触发动画
const showCard = ref(false)

setTimeout(() => {
  showCard.value = true
}, 1000)
</script>

<template>
  <view v-if="showCard" class="animate__animated animate__fadeInUp">
    <text>动态显示的卡片</text>
  </view>
</template>
```

**常用动画类：**
- `animate__fadeIn` - 淡入
- `animate__fadeInUp` - 从下淡入
- `animate__bounceIn` - 弹跳进入
- `animate__zoomIn` - 缩放进入
- `animate__slideInRight` - 从右滑入

**动画速度：**
- `animate__faster` - 更快（500ms）
- `animate__fast` - 快（800ms）
- `animate__slow` - 慢（2s）
- `animate__slower` - 更慢（3s）

**延迟：**
- `animate__delay-1s` - 延迟1秒
- `animate__delay-2s` - 延迟2秒

---

### 5️⃣ 实战：美化登录页面

```vue
<template>
  <view class="min-h-screen bg-gradient-to-b from-primary-light to-white">
    <!-- 顶部Logo区域 -->
    <view class="flex justify-center items-center pt-80 pb-40">
      <image 
        src="/static/logo.png" 
        class="w-120 h-120 animate__animated animate__zoomIn"
      />
    </view>
    
    <!-- 标题 -->
    <view class="text-center px-32 animate__animated animate__fadeInUp">
      <text class="text-4xl font-bold text-gray-800">解密2026</text>
      <text class="text-sm text-gray-600 mt-8 block">探索你的命运密码</text>
    </view>
    
    <!-- 表单卡片 -->
    <view class="mx-32 mt-48 animate__animated animate__fadeInUp animate__delay-1s">
      <view class="glass-effect rounded-2xl p-32 shadow-card">
        <!-- 输入框 -->
        <view class="mb-24">
          <view class="flex items-center bg-white rounded-lg px-20 py-16">
            <image src="/static/icons/user.png" class="w-40 h-40 mr-12" />
            <input 
              placeholder="请输入手机号" 
              class="flex-1 text-base"
            />
          </view>
        </view>
        
        <view class="mb-24">
          <view class="flex items-center bg-white rounded-lg px-20 py-16">
            <image src="/static/icons/code.png" class="w-40 h-40 mr-12" />
            <input 
              placeholder="请输入验证码" 
              class="flex-1 text-base"
            />
            <text class="text-primary text-sm">获取验证码</text>
          </view>
        </view>
        
        <!-- 登录按钮 -->
        <view 
          class="gradient-primary rounded-full py-20 text-center transition-smooth hover:opacity-80"
          @click="handleLogin"
        >
          <text class="text-white text-lg font-bold">立即登录</text>
        </view>
      </view>
    </view>
    
    <!-- 底部提示 -->
    <view class="text-center mt-40 animate__animated animate__fadeIn animate__delay-2s">
      <text class="text-xs text-gray-500">登录即表示同意《用户协议》和《隐私政策》</text>
    </view>
  </view>
</template>

<script setup>
const handleLogin = () => {
  uni.showToast({
    title: '登录成功',
    icon: 'success'
  })
}
</script>
```

---

### 6️⃣ 实战：卡片列表动画

```vue
<template>
  <view class="px-24 py-32">
    <view 
      v-for="(item, index) in list" 
      :key="index"
      :class="[
        'bg-white rounded-xl p-24 mb-16 shadow-soft',
        'animate__animated animate__fadeInUp',
        `animate__delay-${index * 0.1}s`
      ]"
    >
      <view class="flex items-center">
        <!-- 图标 -->
        <view class="w-80 h-80 gradient-gold rounded-full flex justify-center items-center mr-16">
          <text class="text-white text-2xl">{{ item.icon }}</text>
        </view>
        
        <!-- 内容 -->
        <view class="flex-1">
          <text class="text-lg font-bold text-gray-800 block mb-4">{{ item.title }}</text>
          <text class="text-sm text-gray-500">{{ item.desc }}</text>
        </view>
        
        <!-- 箭头 -->
        <text class="text-gray-400">></text>
      </view>
    </view>
  </view>
</template>

<script setup>
import { ref } from 'vue'

const list = ref([
  { icon: '🔮', title: '四柱八字', desc: '查看你的命盘详情' },
  { icon: '🌙', title: '秘籍解锁', desc: '解锁命运密码' },
  { icon: '✨', title: '分享海报', desc: '分享给好友' },
])
</script>
```

---

### 7️⃣ 响应式设计

```vue
<template>
  <!-- 不同屏幕尺寸显示不同样式 -->
  <view class="p-16 sm:p-24 md:p-32">
    <text class="text-base sm:text-lg md:text-xl">响应式文字</text>
  </view>
  
  <!-- Grid布局 -->
  <view class="grid grid-cols-2 gap-16 p-24">
    <view class="bg-white p-16 rounded-lg shadow-soft">
      <text>格子1</text>
    </view>
    <view class="bg-white p-16 rounded-lg shadow-soft">
      <text>格子2</text>
    </view>
    <view class="bg-white p-16 rounded-lg shadow-soft">
      <text>格子3</text>
    </view>
    <view class="bg-white p-16 rounded-lg shadow-soft">
      <text>格子4</text>
    </view>
  </view>
</template>
```

---

### 8️⃣ 常用工具类速查

| 类名 | 效果 | 示例 |
|------|------|------|
| `flex` | 弹性布局 | `<view class="flex">` |
| `flex-col` | 垂直排列 | `<view class="flex flex-col">` |
| `justify-center` | 水平居中 | `<view class="flex justify-center">` |
| `items-center` | 垂直居中 | `<view class="flex items-center">` |
| `w-full` | 100%宽度 | `<view class="w-full">` |
| `h-screen` | 100vh高度 | `<view class="h-screen">` |
| `rounded-lg` | 圆角 | `<view class="rounded-lg">` |
| `shadow-lg` | 阴影 | `<view class="shadow-lg">` |
| `bg-white` | 白色背景 | `<view class="bg-white">` |
| `text-center` | 文字居中 | `<text class="text-center">` |
| `font-bold` | 粗体 | `<text class="font-bold">` |
| `opacity-50` | 50%透明 | `<view class="opacity-50">` |

---

## 🎯 升级现有页面的步骤

### 步骤1：找到要优化的页面
例如：`src/pages/index/index.vue`

### 步骤2：添加Tailwind类
逐步替换内联样式为Tailwind类：

```vue
<!-- 旧代码 -->
<view style="display: flex; padding: 20rpx; background: #fff; border-radius: 10rpx;">

<!-- 新代码 -->
<view class="flex p-20 bg-white rounded-lg">
```

### 步骤3：添加动画效果
在需要动画的元素上添加animate.css类：

```vue
<view class="animate__animated animate__fadeInUp">
  <!-- 内容 -->
</view>
```

### 步骤4：测试
```bash
npm run dev:h5
```

---

##  下一步建议

1. ✅ 从首页开始，逐步优化每个页面
2. ✅ 重点优化登录、结果页等关键页面
3. ✅ 使用动画提升用户体验
4. ✅ 保持与uView组件的兼容性

---

## 📖 官方文档

- [Tailwind CSS 文档](https://tailwindcss.com/docs)
- [Animate.css 文档](https://animate.style/)
- [uni-app 样式文档](https://uniapp.dcloud.net.cn/tutorial/syntax-css.html)
