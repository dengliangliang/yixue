# 统一组件样式使用指南

## 📦 已创建的样式组件

位置：`src/styles/components.css`

已在 `src/main.js` 中全局引入，所有页面可直接使用。

---

## 🎨 按钮样式

### 主按钮（金色渐变）

```vue
<template>
  <view class="btn-primary" @click="handleClick">
    <text class="btn-primary-text">探索 2026</text>
  </view>
</template>
```

**效果**：
- 金色渐变背景
- 深棕色文字
- 点击缩放反馈
- 立体阴影效果

---

### 次要按钮（描边样式）

```vue
<template>
  <view class="btn-secondary" @click="handleClick">
    <text class="btn-secondary-text">取消</text>
  </view>
</template>
```

**效果**：
- 透明背景 + 金色边框
- 毛玻璃效果
- 适合次要操作

---

### 小按钮

```vue
<template>
  <view class="btn-small">
    <text class="btn-small-text">查看详情</text>
  </view>
</template>
```

---

## 📄 卡片样式

### 毛玻璃卡片

```vue
<template>
  <view class="card-glass animate__animated animate__fadeInUp">
    <text class="title-secondary">卡片标题</text>
    <text class="text-sm">卡片内容...</text>
  </view>
</template>
```

**效果**：
- 半透明白色背景
- 毛玻璃模糊效果
- 适合叠加在背景图上

---

### 金色卡片

```vue
<template>
  <view class="card-gold">
    <text class="title-secondary">重要信息</text>
    <text>内容...</text>
  </view>
</template>
```

**效果**：
- 金色渐变背景
- 适合突出显示

---

### 白色卡片

```vue
<template>
  <view class="card-white">
    <text>普通内容</text>
  </view>
</template>
```

---

## 📝 表单样式

### 输入框

```vue
<template>
  <view class="form-item">
    <text class="input-label">姓名</text>
    <input 
      class="input-field" 
      placeholder="请输入姓名"
      v-model="name"
    />
  </view>
</template>
```

---

### 选择器

```vue
<template>
  <view class="form-item">
    <text class="input-label">性别</text>
    <view class="select-field" @click="showPicker">
      <text>{{ gender || '请选择性别' }}</text>
      <text>></text>
    </view>
  </view>
</template>
```

---

## 📊 表格样式

```vue
<template>
  <view class="table-container animate__animated animate__fadeIn">
    <!-- 表头 -->
    <view class="table-header">
      <text class="table-header-cell">时柱</text>
      <text class="table-header-cell">日柱</text>
      <text class="table-header-cell">月柱</text>
      <text class="table-header-cell">年柱</text>
    </view>
    
    <!-- 数据行 -->
    <view class="table-row" v-for="(row, index) in tableData" :key="index">
      <text class="table-cell">{{ row.shi }}</text>
      <text class="table-cell">{{ row.ri }}</text>
      <text class="table-cell">{{ row.yue }}</text>
      <text class="table-cell">{{ row.nian }}</text>
    </view>
  </view>
</template>
```

---

## 🔄 Tab切换样式

```vue
<template>
  <view class="tab-bar">
    <view 
      v-for="(tab, index) in tabs" 
      :key="index"
      :class="['tab-item', currentTab === index ? 'tab-item-active' : '']"
      @click="currentTab = index"
    >
      {{ tab.name }}
    </view>
  </view>
</template>

<script setup>
import { ref } from 'vue'

const currentTab = ref(0)
const tabs = [
  { name: '排盘' },
  { name: '禀赋' },
  { name: '喜忌' }
]
</script>
```

---

## 🌀 加载动画

### 1. 金色旋转加载

```vue
<template>
  <view class="flex justify-center items-center py-48">
    <view class="loading-spinner"></view>
  </view>
</template>
```

**效果**：圆形旋转动画

---

### 2. 脉冲点加载

```vue
<template>
  <view class="flex justify-center items-center py-48">
    <view class="loading-dots">
      <view class="loading-dot"></view>
      <view class="loading-dot"></view>
      <view class="loading-dot"></view>
    </view>
  </view>
</template>
```

**效果**：三个点依次跳动

---

### 3. 波浪条加载

```vue
<template>
  <view class="flex justify-center items-center py-48">
    <view class="loading-wave">
      <view class="loading-wave-bar"></view>
      <view class="loading-wave-bar"></view>
      <view class="loading-wave-bar"></view>
      <view class="loading-wave-bar"></view>
      <view class="loading-wave-bar"></view>
    </view>
  </view>
</template>
```

**效果**：五条金色竖条波浪起伏

---

## 🎯 标题样式

### 主标题

```vue
<text class="title-primary">性格剧本</text>
```

### 副标题

```vue
<text class="title-secondary">优势分析</text>
```

### 小标题

```vue
<text class="title-small">基本信息</text>
```

---

## 🏷️ 徽章样式

### 金色徽章

```vue
<text class="badge-gold">推荐</text>
<text class="badge-gold">NEW</text>
```

### 描边徽章

```vue
<text class="badge-outline">已解锁</text>
```

---

## 📏 分割线

### 普通分割线

```vue
<view class="divider"></view>
```

### 带文字分割线

```vue
<view class="divider-text">
  <text>更多信息</text>
</view>
```

---

## 🖼️ 背景效果

### 统一背景（beijing.jpg）

```vue
<template>
  <view class="page-bg">
    <view class="page-overlay">
      <!-- 页面内容 -->
      <view class="px-32 py-24">
        <view class="card-glass">
          <text>内容...</text>
        </view>
      </view>
    </view>
  </view>
</template>
```

**说明**：
- `page-bg`：设置beijing.jpg背景
- `page-overlay`：添加渐变叠加层，提升内容可读性

---

## 🎬 结合 Animate.css

### 卡片进入动画

```vue
<view class="card-glass animate__animated animate__fadeInUp">
  内容
</view>
```

### 列表项动画

```vue
<view 
  v-for="(item, index) in list" 
  :key="index"
  :class="[
    'card-white mb-16',
    'animate__animated animate__fadeInUp',
    `animate__delay-${index * 0.1}s`
  ]"
>
  {{ item }}
</view>
```

### 按钮动画

```vue
<view class="btn-primary animate__animated animate__pulse animate__infinite">
  <text class="btn-primary-text">立即开始</text>
</view>
```

---

## 📱 完整页面示例

```vue
<template>
  <!-- 背景容器 -->
  <view class="page-bg">
    <view class="page-overlay min-h-screen px-32 py-24">
      
      <!-- 顶部标题 -->
      <view class="animate__animated animate__fadeInDown">
        <text class="title-primary">您的结果</text>
      </view>
      
      <!-- 卡片内容 -->
      <view class="card-glass animate__animated animate__fadeInUp animate__delay-1s">
        <text class="title-secondary">性格分析</text>
        
        <!-- 表单 -->
        <view class="form-item">
          <text class="input-label">姓名</text>
          <input class="input-field" placeholder="请输入" />
        </view>
        
        <!-- 表格 -->
        <view class="table-container mt-24">
          <view class="table-header">
            <text class="table-header-cell">项目</text>
            <text class="table-header-cell">结果</text>
          </view>
          <view class="table-row">
            <text class="table-cell">木</text>
            <text class="table-cell">旺</text>
          </view>
        </view>
      </view>
      
      <!-- 加载动画 -->
      <view class="flex justify-center py-48" v-if="loading">
        <view class="loading-wave">
          <view class="loading-wave-bar"></view>
          <view class="loading-wave-bar"></view>
          <view class="loading-wave-bar"></view>
          <view class="loading-wave-bar"></view>
          <view class="loading-wave-bar"></view>
        </view>
      </view>
      
      <!-- 底部按钮 -->
      <view class="mt-48 animate__animated animate__fadeInUp animate__delay-2s">
        <view class="btn-primary mb-16" @click="handleNext">
          <text class="btn-primary-text">下一步</text>
        </view>
        
        <view class="btn-secondary" @click="handleBack">
          <text class="btn-secondary-text">返回</text>
        </view>
      </view>
      
    </view>
  </view>
</template>

<script setup>
import { ref } from 'vue'

const loading = ref(false)

const handleNext = () => {
  uni.navigateTo({ url: '/pages/result/result' })
}

const handleBack = () => {
  uni.navigateBack()
}
</script>
```

---

## 🎨 颜色变量

| 颜色名称 | 值 | 用途 |
|---------|---|------|
| 金色主色 | #D4AF37 | 按钮、标题、强调 |
| 浅金色 | #F4E4C1 | 渐变起点 |
| 中金色 | #E8D4A5 | 渐变中间 |
| 深棕色 | #4A3C2A | 按钮文字 |
| 米白色 | #F5F5DC | 副标题 |

---

## ⚡ 性能建议

1. **动画延迟**：列表项使用 `animate__delay-{n}s` 控制延迟
2. **按需加载**：大列表使用虚拟滚动
3. **避免嵌套**：不要过度嵌套毛玻璃效果
4. **图片优化**：beijing.jpg 建议压缩到 < 500KB

---

## 🔥 快速替换指南

### 旧按钮 → 新按钮

```vue
<!-- 旧代码 -->
<view class="bom_btn" @click="next">下一页</view>

<!-- 新代码 -->
<view class="btn-primary" @click="next">
  <text class="btn-primary-text">下一页</text>
</view>
```

### 旧输入框 → 新输入框

```vue
<!-- 旧代码 -->
<input style="padding: 20rpx; background: #fff;" />

<!-- 新代码 -->
<view class="form-item">
  <text class="input-label">标签</text>
  <input class="input-field" placeholder="提示文字" />
</view>
```

### 旧卡片 → 新卡片

```vue
<!-- 旧代码 -->
<view style="background: #fff; padding: 20rpx; border-radius: 10rpx;">

<!-- 新代码 -->
<view class="card-glass animate__animated animate__fadeInUp">
```

---

需要更多示例？查看：
- `.knowledge/tailwind-usage-guide.md` - Tailwind基础
- `.knowledge/example-optimized-index.vue` - 首页完整示例
