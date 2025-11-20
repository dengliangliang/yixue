# 小程序前端模块 - 代码地图

## 文件结构

```
1213yixuesuanming/
├── pages/                       # 页面目录
│   ├── index/
│   │   ├── index.vue            # 启动页
│   │   ├── Message.vue          # 消息页
│   │   ├── gossip.vue           # 八卦页
│   │   └── Generated.vue        # 生成页
│   ├── login/
│   │   ├── index.vue            # 登录首页
│   │   ├── login.vue            # 登录页
│   │   └── setInfo.vue          # 设置信息页
│   ├── result/
│   │   ├── result.vue           # 结果页
│   │   ├── miji.vue             # 秘籍页
│   │   ├── yijiesuo.vue         # 已解锁页
│   │   ├── share.vue            # 分享页
│   │   └── share-haibao.vue     # 分享海报
│   └── yinsi.vue                # 隐私政策
│
├── common/                      # 公共资源
│   ├── request/
│   │   └── api.js               # API封装
│   ├── hooks/
│   │   └── index.js             # 组合式函数
│   ├── util.js                  # 工具函数
│   └── style/
│       └── index.css            # 全局样式
│
├── components/                  # 组件目录
│   └── firstui/                 # FirstUI组件
│
├── config/
│   └── website.js               # 网站配置
│
├── static/                      # 静态资源
│   ├── wuxing/                  # 五行图片
│   ├── jieguo/                  # 结果页图片
│   └── ttf/                     # 字体文件
│
├── uni_modules/                 # uni-app模块
│   └── uview-ui/                # uview-ui组件库
│
├── App.vue                      # 应用入口
├── main.js                      # 主入口文件
├── pages.json                   # 页面配置
├── manifest.json                # 应用配置
└── uni.scss                     # 全局样式变量
```

## 页面组件结构

### 启动页 (index.vue)

```vue
<template>
  <view class="content">
    <image @click="navto"></image>
  </view>
</template>

<script>
export default {
  data() {
    return {
      windowHeight: ''
    }
  },
  onLoad(op) {
    // 接收外部参数
    if (op.sign) {
      uni.setStorageSync('app_parmas', op)
    }
  },
  methods: {
    navto() {
      // 跳转到设置信息页
    }
  }
}
</script>
```

### 设置信息页 (setInfo.vue)

```vue
<template>
  <view class="container">
    <!-- 日期选择 -->
    <picker mode="date" @change="onDateChange"></picker>
    
    <!-- 时辰选择 -->
    <picker mode="time" @change="onTimeChange"></picker>
    
    <!-- 性别选择 -->
    <radio-group @change="onGenderChange"></radio-group>
    
    <!-- 地区选择 -->
    <picker mode="multiSelector" @change="onAreaChange"></picker>
    
    <!-- 提交按钮 -->
    <button @click="submitInfo"></button>
  </view>
</template>

<script>
export default {
  data() {
    return {
      birthDate: '',
      birthHour: 0,
      birthMinute: 0,
      gender: 0,
      provinceId: 0,
      cityId: 0
    }
  },
  methods: {
    async submitInfo() {
      // 调用 addRecord API
    }
  }
}
</script>
```

### 结果页 (result.vue)

```vue
<template>
  <view class="pt-50 px-30 w_100">
    <!-- 标题 -->
    <view class="my_color flex a_c fz_24">
      您的 <text class="fz_b fz_36">性格剧本</text> 已解码
    </view>
    
    <!-- 优势（最少五行）-->
    <view :class="`${loadColor(xing_ge_min.wu_xing)}_back`">
      <image :src="`/static/wuxing/${loadColor(xing_ge_min.wu_xing)}-@2x.png`"></image>
      <text>最少</text>
      <view>{{xing_ge_min.xing_result}}</view>
    </view>
    
    <!-- 劣势（最多五行）-->
    <view :class="`${loadColor(xing_ge_max.wu_xing)}_back`">
      <image :src="`/static/wuxing/${loadColor(xing_ge_max.wu_xing)}-@2x.png`"></image>
      <text>最多</text>
      <view>{{xing_ge_max.xing_result}}</view>
    </view>
    
    <!-- 下一页按钮 -->
    <view @click="$go('/pages/result/miji?record_id='+record_id)">
      下一页
    </view>
  </view>
</template>

<script>
export default {
  data() {
    return {
      record_id: '',
      xing_ge_max: {},
      xing_ge_min: {}
    }
  },
  onLoad({ record_id }) {
    this.record_id = record_id;
    this.loadData();
  },
  methods: {
    async loadData() {
      // 调用 getResult API
    },
    loadColor(type) {
      // 五行颜色映射
    }
  }
}
</script>
```

## 数据流

### 参数传递流程

```
外部链接
    ↓ (携带参数: sign, merchantId, activityCode, agentCode, customerNo)
index.vue (onLoad)
    ↓ uni.setStorageSync('app_parmas', op)
setInfo.vue
    ↓ uni.getStorageSync('app_parmas')
API请求 (addRecord)
    ↓ 返回 record_id
result.vue
    ↓ 接收 record_id
API请求 (getResult)
    ↓ 返回测算结果
展示结果
```

### 状态管理

```
本地存储 (uni.setStorageSync/getStorageSync)
├── app_parmas           # 外部参数
│   ├── sign
│   ├── merchantId
│   ├── activityCode
│   ├── agentCode
│   └── customerNo
│
├── token                # 用户Token
│
└── userInfo             # 用户信息
    ├── id
    ├── username
    ├── nickname
    └── avatar
```

## API调用

### API封装 (common/request/api.js)

```javascript
// GET请求
export const get = (url, data, timeout = 100000) => {
  const header = {
    'token': uni.getStorageSync('token')
  }
  return new Promise((resolve, reject) => {
    uni.request({
      url: URL + url,
      data,
      header,
      timeout,
      method: 'GET',
      success: (res) => {
        // 401自动跳转登录
        if (res.data.code == 401) {
          // 跳转登录页
        }
        resolve(res.data);
      },
      fail: (error) => {
        reject(error)
      }
    })
  })
}

// POST请求
export const post = (url, data, timeout = 100000) => {
  // 类似GET请求
}

// 文件上传
export const file = (url, filePath, formData, timeout = 100000) => {
  const header = {
    'token': uni.getStorageSync('token')
  }
  return new Promise((resolve, reject) => {
    uni.uploadFile({
      url,
      filePath,
      name: 'file',
      header,
      formData,
      success: (uploadFileRes) => {
        resolve(JSON.parse(uploadFileRes.data))
      },
      fail: (err) => {
        reject(err)
      }
    });
  })
}
```

### API使用示例

```javascript
// 在页面中使用
export default {
  methods: {
    async loadData() {
      // GET请求
      const res = await this.$api.get('api/user/getProvinceList');
      
      // POST请求
      const res = await this.$api.post('api/user/addRecord', {
        customerNo: this.customerNo,
        date: this.birthDate,
        // ... 其他参数
      });
      
      // 处理响应
      if (res.code == 1) {
        // 成功
      } else {
        // 失败
        this.$toast(res.msg);
      }
    }
  }
}
```

## 路由导航

### 页面跳转方式

```javascript
// 1. 保留当前页面，跳转到应用内的某个页面
uni.navigateTo({
  url: '/pages/result/result?record_id=123'
});

// 2. 关闭当前页面，跳转到应用内的某个页面
uni.redirectTo({
  url: '/pages/login/login'
});

// 3. 关闭所有页面，打开到应用内的某个页面
uni.reLaunch({
  url: '/pages/index/index'
});

// 4. 跳转到 tabBar 页面，并关闭其他所有非 tabBar 页面
uni.switchTab({
  url: '/pages/index/index'
});

// 5. 关闭当前页面，返回上一页面或多级页面
uni.navigateBack({
  delta: 1
});
```

### 全局方法 $go

```javascript
// 在 main.js 中注册
Vue.prototype.$go = function(url) {
  uni.navigateTo({ url });
}

// 在页面中使用
this.$go('/pages/result/result?record_id=' + this.record_id);
```

## 样式系统

### 全局样式 (App.vue)

```scss
<style lang="scss">
@import url("common/style/index.css");
@import "uview-ui/index.scss";

@font-face {
  font-family: 'YouSheBiaoTiHei';
  src: url('@/static/ttf/font2.ttf');
}

page {
  font-family: YouSheBiaoTiHei;
}
</style>
```

### 样式类命名规范

```scss
// 布局类
.w_100          // width: 100%
.flex           // display: flex
.flex_center    // justify-content: center; align-items: center
.flex_column    // flex-direction: column
.a_c            // align-items: center
.a_bom          // align-items: flex-end

// 间距类
.px-30          // padding-left: 30rpx; padding-right: 30rpx
.py-24          // padding-top: 24rpx; padding-bottom: 24rpx
.mt-25          // margin-top: 25rpx
.mb-16          // margin-bottom: 16rpx

// 字体类
.fz_24          // font-size: 24rpx
.fz_32          // font-size: 32rpx
.fz_36          // font-size: 36rpx
.fz_b           // font-weight: bold
.fz_500         // font-weight: 500

// 颜色类
.c_0            // color: #000000
.c_huo          // color: #FF6B6B (火)
.c_jin          // color: #FFD700 (金)
.c_mu           // color: #4CAF50 (木)
.c_shui         // color: #2196F3 (水)
.c_tu           // color: #8D6E63 (土)

// 背景类
.back_f         // background-color: #FFFFFF
.huo_back       // 火元素背景
.jin_back       // 金元素背景
.mu_back        // 木元素背景
.shui_back      // 水元素背景
.tu_back        // 土元素背景

// 定位类
.po_ab          // position: absolute
.po_re          // position: relative
.po_fi          // position: fixed
.t-0            // top: 0
.l-0            // left: 0
.b-0            // bottom: 0

// 其他
.round-4        // border-radius: 4rpx
.round-t-10     // border-top-left-radius: 10rpx; border-top-right-radius: 10rpx
.shadow-10      // box-shadow: 0 -10rpx 20rpx rgba(0,0,0,0.1)
.zIndex-10      // z-index: 10
.zIndex-11      // z-index: 11
```

### 五行颜色系统

```javascript
// 五行颜色映射
loadColor(type) {
  const colorMap = {
    '金': 'jin',
    '木': 'mu',
    '水': 'shui',
    '火': 'huo',
    '土': 'tu'
  };
  return colorMap[type];
}
```

```scss
// 五行颜色定义
.c_jin { color: #FFD700; }
.c_mu { color: #4CAF50; }
.c_shui { color: #2196F3; }
.c_huo { color: #FF6B6B; }
.c_tu { color: #8D6E63; }

// 五行背景
.jin_back { background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); }
.mu_back { background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%); }
.shui_back { background: linear-gradient(135deg, #2196F3 0%, #1565C0 100%); }
.huo_back { background: linear-gradient(135deg, #FF6B6B 0%, #D32F2F 100%); }
.tu_back { background: linear-gradient(135deg, #8D6E63 0%, #5D4037 100%); }
```

## 生命周期

### 应用生命周期 (App.vue)

```javascript
export default {
  onLaunch: function() {
    console.log('App Launch')
    // 应用启动时执行
    
    // 获取系统信息
    uni.getSystemInfo({
      success: res => {
        Vue.prototype.$windowHeight = res.windowHeight
      }
    });
  },
  onShow: function() {
    console.log('App Show')
    // 应用显示时执行
  },
  onHide: function() {
    console.log('App Hide')
    // 应用隐藏时执行
  }
}
```

### 页面生命周期

```javascript
export default {
  onLoad(options) {
    // 页面加载时执行
    // 接收页面参数
  },
  onReady() {
    // 页面初次渲染完成时执行
  },
  onShow() {
    // 页面显示时执行
  },
  onHide() {
    // 页面隐藏时执行
  },
  onUnload() {
    // 页面卸载时执行
  },
  onPullDownRefresh() {
    // 下拉刷新时执行
  },
  onReachBottom() {
    // 上拉触底时执行
  },
  onShareAppMessage() {
    // 用户点击右上角分享时执行
  }
}
```

## 工具函数

### 全局注册 (main.js)

```javascript
import util from '@/common/util.js';
import * as API from "@/common/request/api.js";
import website from './config/website';

Vue.prototype.util = util;
Vue.prototype.$api = API;
Vue.prototype.$config = website;
Vue.prototype.$toast = function(title, duration = 1500) {
  uni.showToast({
    title,
    icon: 'none',
    duration
  });
};
```

### 工具函数示例 (util.js)

```javascript
export default {
  // 格式化日期
  formatDate(timestamp, format = 'YYYY-MM-DD HH:mm:ss') {
    // 实现
  },
  
  // 防抖
  debounce(fn, delay = 300) {
    let timer = null;
    return function(...args) {
      clearTimeout(timer);
      timer = setTimeout(() => {
        fn.apply(this, args);
      }, delay);
    };
  },
  
  // 节流
  throttle(fn, delay = 300) {
    let last = 0;
    return function(...args) {
      const now = Date.now();
      if (now - last > delay) {
        fn.apply(this, args);
        last = now;
      }
    };
  }
}
```

## 性能优化

### 图片优化
```javascript
// 使用 mode 属性优化图片显示
<image src="/static/image.png" mode="aspectFill"></image>
<image src="/static/image.png" mode="aspectFit"></image>
<image src="/static/image.png" mode="widthFix"></image>

// 懒加载
<image src="/static/image.png" lazy-load></image>
```

### 列表优化
```vue
<!-- 使用虚拟列表 -->
<recycle-list :list="list" :item-height="100">
  <template v-slot="{ item }">
    <view>{{ item.name }}</view>
  </template>
</recycle-list>
```

### 请求优化
```javascript
// 请求防抖
methods: {
  loadData: this.util.debounce(async function() {
    const res = await this.$api.get('api/data');
    // 处理数据
  }, 500)
}
```

## 错误处理

### 全局错误处理
```javascript
// 在 main.js 中
Vue.config.errorHandler = function(err, vm, info) {
  console.error('Error:', err);
  console.error('Component:', vm);
  console.error('Info:', info);
  
  // 上报错误
  // reportError(err, vm, info);
};
```

### API错误处理
```javascript
async loadData() {
  try {
    const res = await this.$api.post('api/data', params);
    if (res.code == 1) {
      // 成功
    } else {
      // 业务错误
      this.$toast(res.msg);
    }
  } catch (error) {
    // 网络错误
    this.$toast('网络错误，请稍后重试');
    console.error(error);
  }
}
```

## 相关文档
- [功能说明](./README.md)
- [API文档](../api-documentation/)
- [样式规范](./style-guide.md)
