# 小程序前端模块

## 模块概述

小程序前端基于 uni-app 框架开发，使用 Vue 2 语法，支持微信小程序平台。

### 核心功能
1. 用户信息输入
2. 测算结果展示
3. 性格分析展示
4. 分享海报生成

## 技术栈

### 框架和库
- **uni-app**: 跨平台开发框架
- **Vue 2**: 前端框架
- **uview-ui**: UI组件库
- **@vue/composition-api**: Composition API支持

### 项目配置
- **manifest.json**: 应用配置
- **pages.json**: 页面路由配置
- **uni.scss**: 全局样式变量

## 项目结构

```
1213yixuesuanming/
├── pages/                  # 页面目录
│   ├── index/             # 首页
│   │   ├── index.vue      # 启动页
│   │   ├── Message.vue    # 消息页
│   │   ├── gossip.vue     # 八卦页
│   │   └── Generated.vue  # 生成页
│   ├── login/             # 登录相关
│   │   ├── index.vue      # 登录首页
│   │   ├── login.vue      # 登录页
│   │   └── setInfo.vue    # 设置信息页
│   ├── result/            # 结果展示
│   │   ├── result.vue     # 结果页
│   │   ├── miji.vue       # 秘籍页
│   │   ├── yijiesuo.vue   # 已解锁页
│   │   ├── share.vue      # 分享页
│   │   └── share-haibao.vue # 分享海报
│   └── yinsi.vue          # 隐私政策
├── common/                 # 公共资源
│   ├── request/           # 请求封装
│   │   └── api.js         # API封装
│   ├── hooks/             # 组合式函数
│   ├── util.js            # 工具函数
│   └── style/             # 样式文件
├── components/            # 组件
├── config/                # 配置
│   └── website.js         # 网站配置
├── static/                # 静态资源
└── uni_modules/           # uni-app模块
```

## 页面流程

### 用户测算流程
```
启动页 (index.vue)
    ↓ 点击按钮
设置信息页 (setInfo.vue)
    ↓ 输入出生信息
    ↓ 调用 addRecord API
结果页 (result.vue)
    ↓ 展示性格分析
秘籍页 (miji.vue)
    ↓ 展示详细解读
已解锁页 (yijiesuo.vue)
    ↓ 展示完整信息
分享页 (share.vue)
    ↓ 生成分享海报
分享海报页 (share-haibao.vue)
```

## 核心页面

### 1. 启动页 (index.vue)

**功能**: 应用启动页，接收外部参数

**关键代码**:
```vue
<script>
export default {
    onLoad(op) {
        // 接收外部参数
        if (op.sign) {
            uni.setStorageSync('app_parmas', op)
        } else {
            this.$toast('获取参数失败~')
        }
    },
    methods: {
        navto() {
            uni.navigateTo({
                url: "/pages/login/setInfo"
            })
        }
    }
}
</script>
```

**参数接收**:
- `sign`: 签名参数
- 其他业务参数（merchantId, activityCode等）

### 2. 设置信息页 (setInfo.vue)

**功能**: 用户输入出生信息

**输入字段**:
- 出生日期（阳历）
- 出生时辰
- 性别
- 出生地区（省份+城市）

**提交逻辑**:
```javascript
async submitInfo() {
    // 获取存储的参数
    const app_params = uni.getStorageSync('app_parmas');
    
    // 构建请求参数
    const params = {
        customerNo: app_params.customerNo,
        date: this.birthDate,
        hour: this.birthHour,
        minute: this.birthMinute,
        gender: this.gender,
        area_id: this.cityId,
        merchantId: app_params.merchantId,
        activityCode: app_params.activityCode,
        agentCode: app_params.agentCode,
        sign: app_params.sign
    };
    
    // 调用API
    const res = await this.$api.post('api/user/addRecord', params);
    
    if (res.code == 1) {
        // 跳转到结果页
        uni.navigateTo({
            url: `/pages/result/result?record_id=${res.data.record_id}`
        });
    }
}
```

### 3. 结果页 (result.vue)

**功能**: 展示性格分析结果

**数据加载**:
```javascript
async loadData() {
    const res = await this.$api.post('api/si_zhu/getResult', {
        record_id: this.record_id
    });
    
    if (res.code != 1) return this.$toast(res.msg);
    
    this.info = res.data;
    this.xing_ge_max = res.data.xing_ge_max; // 最多五行（劣势）
    this.xing_ge_min = res.data.xing_ge_min; // 最少五行（优势）
}
```

**五行颜色映射**:
```javascript
loadColor(type) {
    let color_;
    switch (type) {
        case '金': color_ = 'jin'; break;
        case '木': color_ = 'mu'; break;
        case '水': color_ = 'shui'; break;
        case '火': color_ = 'huo'; break;
        case '土': color_ = 'tu'; break;
    }
    return color_;
}
```

**UI结构**:
```vue
<template>
    <view class="pt-50 px-30 w_100">
        <!-- 标题 -->
        <view class="my_color flex a_c fz_24">
            您的 <text class="fz_b fz_36">性格剧本</text> 已解码
        </view>
        
        <!-- 优势（最少五行）-->
        <view class="w_100 round-4 mb-24" :class="`${loadColor(xing_ge_min.wu_xing)}_back`">
            <image :src="`/static/wuxing/${loadColor(xing_ge_min.wu_xing)}-@2x.png`"></image>
            <text>最少</text>
            <view>{{xing_ge_min.xing_result}}</view>
        </view>
        
        <!-- 劣势（最多五行）-->
        <view class="w_100 round-4 mb-24" :class="`${loadColor(xing_ge_max.wu_xing)}_back`">
            <image :src="`/static/wuxing/${loadColor(xing_ge_max.wu_xing)}-@2x.png`"></image>
            <text>最多</text>
            <view>{{xing_ge_max.xing_result}}</view>
        </view>
        
        <!-- 下一页按钮 -->
        <view @click="$go('/pages/result/miji?record_id='+record_id)" class="bom_btn">
            下一页
        </view>
    </view>
</template>
```

## API封装

### 请求封装 (common/request/api.js)

**GET请求**:
```javascript
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
                    uni.showToast({
                        title: "请先登录",
                        icon: "none",
                        success() {
                            setTimeout(() => uni.navigateTo({
                                url: '/pages_sub/login/login'
                            }), 900)
                        }
                    })
                }
                resolve(res.data);
            },
            fail: (error) => {
                uni.showToast({
                    title: error,
                    icon: "none"
                })
                reject(error)
            }
        })
    })
}
```

**POST请求**:
```javascript
export const post = (url, data, timeout = 100000) => {
    const header = {
        'token': uni.getStorageSync('token')
    }
    return new Promise((resolve, reject) => {
        uni.request({
            url: URL + url,
            data,
            header,
            timeout,
            method: 'POST',
            success: (res) => {
                // 401自动跳转登录
                if (res.data.code == 401) {
                    // 同GET
                }
                resolve(res.data);
            },
            fail: (error) => {
                resolve(error)
            }
        })
    })
}
```

**特点**:
- 自动添加 Token
- 401 自动跳转登录
- 统一错误处理
- Promise 封装

## 配置管理

### 网站配置 (config/website.js)

```javascript
export default {
    title: '来自2025的好消息',
    
    // 生产环境
    appHotVersion: '1.0.0.0',
    URL: 'https://yixue.couxi.com',
    upFeilUrl: 'https://yixue.couxi.com/api/index/saveBase64',
    
    // 测试环境（注释）
    // URL: 'https://1213yixue.dev.jiangkukeji.cn/',
    // upFeilUrl: 'https://1213yixue.dev.jiangkukeji.cn//api/common/upload',
};
```

### 页面配置 (pages.json)

```json
{
    "pages": [
        {
            "path": "pages/index/index",
            "style": {
                "navigationBarTitleText": "来自2025的好消息",
                "navigationBarBackgroundColor": "#E2C289"
            }
        }
    ],
    "globalStyle": {
        "navigationStyle": "custom",
        "navigationBarTextStyle": "black",
        "navigationBarBackgroundColor": "#E2C289",
        "backgroundColor": "#F8F8F8"
    }
}
```

## 样式规范

### 全局样式 (App.vue)

```vue
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

### 样式约束
- **仅使用 class 选择器**: 小程序不支持通配符和标签选择器
- **避免使用 * 选择器**
- **避免使用 p 标签选择器**

**正确示例**:
```scss
.user-info { }
.btn-primary { }
```

**错误示例**:
```scss
* { }        // 不支持
p { }        // 不支持
#header { }  // 性能差
```

## 数据流

### 1. 参数传递流程
```
外部链接参数
    ↓
index.vue (onLoad)
    ↓
uni.setStorageSync('app_parmas', op)
    ↓
setInfo.vue (提交时读取)
    ↓
API请求
```

### 2. 测算结果流程
```
addRecord API
    ↓
返回 record_id
    ↓
跳转 result.vue?record_id=xxx
    ↓
getResult API
    ↓
展示结果
```

## 工具函数

### 全局方法注册 (main.js)

```javascript
import util from '@/common/util.js';
import * as API from "@/common/request/api.js";
import website from './config/website';

Vue.prototype.util = util;
Vue.prototype.$api = API;
Vue.prototype.$config = website;
```

### 使用示例

```javascript
// 调用API
const res = await this.$api.post('api/user/addRecord', params);

// 获取配置
const baseUrl = this.$config.URL;

// 使用工具函数
this.util.formatDate(timestamp);
```

## 注意事项

### 1. 小程序限制
- 不支持通配符选择器 `*`
- 不支持标签选择器（如 `p`, `div`）
- 仅支持 class 选择器
- 不支持某些 CSS3 特性

### 2. 样式命名
- 使用语义化的 class 名称
- 避免使用 ID 选择器
- 统一使用下划线或短横线分隔

### 3. API调用
- 所有请求自动携带 Token
- 401 错误自动跳转登录
- 使用 async/await 处理异步

### 4. 路由跳转
- 使用 `uni.navigateTo` 跳转页面
- 使用 `uni.redirectTo` 重定向
- 使用 `uni.switchTab` 切换 Tab

### 5. 数据存储
- 使用 `uni.setStorageSync` 同步存储
- 使用 `uni.getStorageSync` 同步读取
- 敏感数据需加密存储

## 性能优化

### 1. 图片优化
- 使用 WebP 格式
- 压缩图片大小
- 使用懒加载

### 2. 请求优化
- 合并请求
- 使用缓存
- 设置合理的超时时间

### 3. 渲染优化
- 使用 v-if 减少渲染
- 避免深层嵌套
- 合理使用组件

## 相关文档
- [uni-app 官方文档](https://uniapp.dcloud.net.cn/)
- [微信小程序开发文档](https://developers.weixin.qq.com/miniprogram/dev/framework/)
- [uview-ui 文档](https://www.uviewui.com/)
- [API接口文档](../api-documentation/)
