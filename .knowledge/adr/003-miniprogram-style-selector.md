# ADR-003: 小程序样式选择器限制

## 状态
已采纳并强制执行

## 上下文

项目前端使用 uni-app 框架开发微信小程序。在开发过程中发现，某些CSS选择器在小程序环境中不被支持或表现异常：

1. **通配符选择器** (`*`): 不支持
2. **标签选择器** (`p`, `div`): 支持有限，性能差
3. **ID选择器** (`#id`): 支持但不推荐
4. **属性选择器** (`[attr]`): 部分支持
5. **伪类选择器** (`:hover`, `:active`): 部分支持

这些限制导致：
- 样式无法正常应用
- 页面渲染异常
- 性能问题
- 跨平台兼容性差

## 决策

**强制要求**小程序前端样式只使用 **class 选择器**，禁止使用通配符选择器和标签选择器。

## 理由

### 1. 小程序平台限制
微信小程序对CSS选择器有严格限制：

```scss
// ❌ 不支持：通配符选择器
* {
  margin: 0;
  padding: 0;
}

// ❌ 不推荐：标签选择器
p {
  font-size: 14px;
}
div {
  display: flex;
}

// ✅ 推荐：class选择器
.text {
  font-size: 14px;
}
.container {
  display: flex;
}
```

### 2. 性能考虑
class选择器性能最优：

```scss
// 性能排序（从快到慢）：
// 1. class选择器    .my-class
// 2. ID选择器       #my-id
// 3. 标签选择器     div
// 4. 属性选择器     [attr="value"]
// 5. 伪类选择器     :hover
```

### 3. 跨平台兼容性
uni-app 支持多端编译，class选择器兼容性最好：

| 选择器类型 | 微信小程序 | H5 | App |
|-----------|-----------|----|----|
| class选择器 | ✅ | ✅ | ✅ |
| ID选择器 | ✅ | ✅ | ✅ |
| 标签选择器 | ⚠️ | ✅ | ✅ |
| 通配符选择器 | ❌ | ✅ | ✅ |

### 4. 代码可维护性
统一使用class选择器，代码更清晰：

```vue
<template>
  <!-- ✅ 清晰的class命名 -->
  <view class="user-card">
    <view class="user-avatar"></view>
    <view class="user-info">
      <text class="user-name">张三</text>
      <text class="user-bio">这是个人简介</text>
    </view>
  </view>
</template>

<style scoped>
.user-card {
  padding: 20rpx;
}
.user-avatar {
  width: 100rpx;
  height: 100rpx;
}
.user-info {
  flex: 1;
}
.user-name {
  font-size: 32rpx;
  font-weight: bold;
}
.user-bio {
  font-size: 24rpx;
  color: #999;
}
</style>
```

### 5. BEM命名规范
采用BEM（Block Element Modifier）命名规范：

```scss
// Block（块）
.user-card { }

// Element（元素）
.user-card__avatar { }
.user-card__info { }
.user-card__name { }

// Modifier（修饰符）
.user-card--active { }
.user-card--disabled { }
```

## 备选方案

### 方案1: 允许标签选择器
**优点**:
- 代码量少
- 符合传统Web开发习惯

**缺点**:
- 小程序性能差
- 跨平台兼容性问题
- 容易出现样式冲突

**结论**: 不采纳。性能和兼容性问题严重。

### 方案2: 使用ID选择器
**优点**:
- 选择器优先级高
- 性能较好

**缺点**:
- ID不可复用
- 难以维护
- 不符合组件化思想

**结论**: 不采纳。不利于组件复用。

### 方案3: 混合使用多种选择器
**优点**:
- 灵活性高

**缺点**:
- 规范难以统一
- 容易出错
- 维护困难

**结论**: 不采纳。不利于团队协作。

## 实施细节

### 1. 基本规范

#### 命名规范
```scss
// ✅ 推荐：语义化命名
.user-profile { }
.product-card { }
.btn-primary { }

// ❌ 避免：无意义命名
.box1 { }
.item { }
.a { }

// ✅ 推荐：使用短横线分隔
.user-name { }
.product-list { }

// ❌ 避免：驼峰命名（虽然支持，但不推荐）
.userName { }
.productList { }
```

#### 层级嵌套
```scss
// ✅ 推荐：扁平化结构
.card { }
.card-header { }
.card-body { }
.card-footer { }

// ❌ 避免：深层嵌套
.card .header .title .text { }
```

### 2. 常用样式类

#### 布局类
```scss
// 宽度
.w_100 { width: 100%; }
.w_50 { width: 50%; }

// Flex布局
.flex { display: flex; }
.flex_center {
  display: flex;
  justify-content: center;
  align-items: center;
}
.flex_column { flex-direction: column; }
.flex_1 { flex: 1; }

// 对齐
.a_c { align-items: center; }
.a_bom { align-items: flex-end; }
.j_c { justify-content: center; }
.j_between { justify-content: space-between; }
```

#### 间距类
```scss
// Padding
.px-30 {
  padding-left: 30rpx;
  padding-right: 30rpx;
}
.py-24 {
  padding-top: 24rpx;
  padding-bottom: 24rpx;
}
.pt-50 { padding-top: 50rpx; }

// Margin
.mt-25 { margin-top: 25rpx; }
.mb-16 { margin-bottom: 16rpx; }
.mx-auto {
  margin-left: auto;
  margin-right: auto;
}
```

#### 文字类
```scss
// 字号
.fz_24 { font-size: 24rpx; }
.fz_32 { font-size: 32rpx; }
.fz_36 { font-size: 36rpx; }

// 字重
.fz_b { font-weight: bold; }
.fz_500 { font-weight: 500; }

// 颜色
.c_0 { color: #000000; }
.c_3 { color: #333333; }
.c_6 { color: #666666; }
.c_9 { color: #999999; }
```

#### 定位类
```scss
// 定位
.po_ab { position: absolute; }
.po_re { position: relative; }
.po_fi { position: fixed; }

// 位置
.t-0 { top: 0; }
.l-0 { left: 0; }
.r-0 { right: 0; }
.b-0 { bottom: 0; }

// 层级
.zIndex-10 { z-index: 10; }
.zIndex-11 { z-index: 11; }
```

### 3. 五行颜色系统

项目特有的五行颜色类：

```scss
// 五行文字颜色
.c_jin { color: #FFD700; }    // 金
.c_mu { color: #4CAF50; }     // 木
.c_shui { color: #2196F3; }   // 水
.c_huo { color: #FF6B6B; }    // 火
.c_tu { color: #8D6E63; }     // 土

// 五行背景
.jin_back {
  background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
}
.mu_back {
  background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);
}
.shui_back {
  background: linear-gradient(135deg, #2196F3 0%, #1565C0 100%);
}
.huo_back {
  background: linear-gradient(135deg, #FF6B6B 0%, #D32F2F 100%);
}
.tu_back {
  background: linear-gradient(135deg, #8D6E63 0%, #5D4037 100%);
}
```

### 4. 动态class绑定

```vue
<template>
  <view :class="`${loadColor(wu_xing)}_back`">
    <text :class="`c_${loadColor(wu_xing)}`">
      {{ wu_xing }}
    </text>
  </view>
</template>

<script>
export default {
  methods: {
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
  }
}
</script>
```

### 5. 代码审查检查点

在代码审查时，必须检查以下内容：

✅ **必须做**:
- [ ] 只使用class选择器
- [ ] class命名语义化
- [ ] 使用短横线分隔
- [ ] 避免深层嵌套

❌ **禁止做**:
- [ ] 使用通配符选择器 `*`
- [ ] 使用标签选择器 `p`, `div`
- [ ] 使用ID选择器 `#id`
- [ ] 使用属性选择器 `[attr]`

### 6. 错误示例和修正

#### 错误示例1: 通配符选择器
```scss
// ❌ 错误
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

// ✅ 正确：在需要的元素上添加class
.reset {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}
```

#### 错误示例2: 标签选择器
```scss
// ❌ 错误
p {
  font-size: 14px;
  line-height: 1.5;
}

// ✅ 正确
.text {
  font-size: 14px;
  line-height: 1.5;
}
```

#### 错误示例3: 深层嵌套
```scss
// ❌ 错误
.container .content .item .title .text {
  color: #333;
}

// ✅ 正确
.item-title-text {
  color: #333;
}
```

## 影响

### 正面影响
1. **性能提升**: class选择器性能最优
2. **兼容性好**: 跨平台无问题
3. **可维护性强**: 代码清晰易懂
4. **复用性高**: 组件化开发更容易

### 负面影响
1. **代码量增加**: 需要为每个元素添加class
2. **命名困难**: 需要思考语义化命名
3. **学习成本**: 团队需要适应新规范

### 风险缓解
1. **提供样式库**: 预定义常用样式类
2. **命名规范**: 采用BEM等成熟规范
3. **代码审查**: 严格审查样式代码
4. **工具支持**: 使用IDE插件辅助

## 强制执行

### 1. ESLint规则
可以配置ESLint检测不规范的选择器：

```javascript
// .eslintrc.js
module.exports = {
  rules: {
    'selector-max-universal': 0,  // 禁止通配符
    'selector-max-type': 0,       // 禁止标签选择器
  }
}
```

### 2. 代码审查清单
- [ ] 没有通配符选择器
- [ ] 没有标签选择器
- [ ] class命名规范
- [ ] 没有深层嵌套

### 3. 培训要求
所有前端开发人员必须：
- 理解小程序样式限制
- 掌握class命名规范
- 熟悉常用样式类

## 经验教训

### 成功经验
1. 统一的样式规范提高了代码质量
2. 预定义样式类加速了开发效率
3. BEM命名规范降低了命名难度

### 改进建议
1. 应该建立完整的样式库
2. 可以开发样式生成工具
3. 应该定期更新样式规范

## 相关决策
- [ADR-002: 数据库参数化查询规范](./002-database-parameterized-query.md)
- [ADR-004: 真太阳时修正方案](./004-true-solar-time-correction.md)

## 参考资料
- [微信小程序样式文档](https://developers.weixin.qq.com/miniprogram/dev/framework/view/wxss.html)
- [uni-app样式文档](https://uniapp.dcloud.net.cn/tutorial/syntax-css.html)
- [BEM命名规范](http://getbem.com/)
- [CSS选择器性能](https://developer.mozilla.org/zh-CN/docs/Web/CSS/CSS_Selectors)

---

**决策日期**: 2024-01-12  
**决策人**: 前端技术负责人  
**审核人**: 项目经理  
**最后更新**: 2025-01-19
