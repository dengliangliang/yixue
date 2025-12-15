# 🎨 国风字体下载和配置指南

## 📥 推荐字体下载

### 方案1：快速方案（2个字体）

#### 正文字体：霞鹜文楷
**下载地址：**
- GitHub: https://github.com/lxgw/LxgwWenKai/releases
- 备用: https://wangchujiang.com/free-font/

**下载文件：**
- `LXGWWenKai-Regular.ttf` (常规)
- `LXGWWenKai-Bold.ttf` (粗体)

**重命名为：**
- `XiaWuWenKai-Regular.ttf`
- `XiaWuWenKai-Bold.ttf`

#### 按钮字体：站酷高端黑
**下载地址：**
- 站酷官网: https://www.zcool.com.cn/special/zcoolfonts/
- 直接下载: https://wangchujiang.com/free-font/

**下载文件：**
- `ZhanKuGaoDuanHei.ttf`

**✅ 只需这2个字体即可满足基本需求！**

---

### 方案2：完整方案（7个字体）

#### 1. 霞鹜文楷（正文 - 必选）
- GitHub: https://github.com/lxgw/LxgwWenKai/releases
- 特点：优雅的楷体，清晰易读
- 文件：`LXGWWenKai-Regular.ttf`、`LXGWWenKai-Bold.ttf`

#### 2. 站酷高端黑（按钮 - 必选）
- 站酷: https://www.zcool.com.cn/special/zcoolfonts/
- 特点：粗壮有力，适合按钮
- 文件：`ZhanKuGaoDuanHei.ttf`

#### 3. 思源黑体（标题 - 推荐）
- GitHub: https://github.com/adobe-fonts/source-han-sans/releases
- 特点：Adobe出品，现代大气
- 文件：`SourceHanSansSC-Regular.otf`、`SourceHanSansSC-Bold.otf`

#### 4. 阿里巴巴普惠体（副标题 - 推荐）
- 官网: https://fonts.alibabagroup.com/
- GitHub: https://github.com/hongzhi725/AlibabaPuHuiTi
- 特点：现代国风，免费商用
- 文件：`AlibabaPuHuiTi-Regular.ttf`、`AlibabaPuHuiTi-Bold.ttf`

#### 5. 悠然小楷体（优雅标题 - 可选）
- 下载: https://www.thosefree.com/5-chinese-fonts
- 特点：有力的标题字体
- 文件：`YouRanXiaoKai.ttf`

#### 6. 江西拙楷（手写风格 - 可选）
- 下载: https://www.thosefree.com/5-chinese-fonts
- 特点：手写楷体，自然
- 文件：`JiangXiZhuoKai.ttf`

#### 7. 方正复古粗圆（复古按钮 - 可选）
- 官网: https://www.fangzhengziti.com/
- 特点：复古粗壮
- 文件：`FZFuGuCuYuan.ttf`

---

## 📁 安装步骤

### 步骤1：下载字体文件
从上面的地址下载需要的字体文件（.ttf 或 .otf 格式）

### 步骤2：放入项目
将下载的字体文件复制到：
```
D:\workspace\1213易学\1213yixuesuanming\1213yixuesuanming\src\static\ttf\
```

**重要：文件命名要匹配**
```
霞鹜文楷：
  LXGWWenKai-Regular.ttf → XiaWuWenKai-Regular.ttf
  LXGWWenKai-Bold.ttf → XiaWuWenKai-Bold.ttf

站酷高端黑：
  站酷高端黑.ttf → ZhanKuGaoDuanHei.ttf

思源黑体：
  SourceHanSansSC-Regular.otf (保持原名)
  SourceHanSansSC-Bold.otf (保持原名)

阿里巴巴普惠体：
  Alibaba-PuHuiTi-Regular.ttf → AlibabaPuHuiTi-Regular.ttf
  Alibaba-PuHuiTi-Bold.ttf → AlibabaPuHuiTi-Bold.ttf
```

### 步骤3：引入字体配置
在 `src/main.js` 中添加：
```javascript
// 引入自定义字体
import '@/styles/fonts.css'
```

### 步骤4：测试
运行项目查看效果：
```bash
npm run dev:h5
```

---

## 🎨 字体使用场景

### 正文（霞鹜文楷）
```vue
<text class="text-body">这是优雅的正文内容</text>
<text class="text-sm">小字号描述文字</text>
```

### 按钮（站酷高端黑）
```vue
<view class="btn-primary">
  <text class="btn-primary-text">立即探索</text>
</view>
```
**自动应用**，无需额外class

### 标题（思源黑体粗体）
```vue
<text class="title-primary">主标题</text>
<text class="title-bold">强调标题</text>
```

### 副标题（阿里巴巴普惠体）
```vue
<text class="title-secondary">副标题</text>
```

### 优雅标题（悠然小楷）
```vue
<text class="text-elegant">优雅的标题</text>
```

### 手写风格（江西拙楷）
```vue
<text class="text-handwriting">手写风格文字</text>
<text class="zhuanti-char">九紫离火</text>
```

---

## 📊 字体对照表

| 场景 | 字体 | 类名 | 特点 |
|------|------|------|------|
| 正文内容 | 霞鹜文楷 | `text-body` | 优雅清晰 |
| 按钮文字 | 站酷高端黑 | `btn-primary-text` | 粗壮有力 |
| 主标题 | 思源黑体粗体 | `title-primary` | 现代大气 |
| 副标题 | 阿里巴巴普惠体 | `title-secondary` | 简洁现代 |
| 优雅标题 | 悠然小楷 | `text-elegant` | 传统优雅 |
| 手写风格 | 江西拙楷 | `text-handwriting` | 自然亲切 |
| Tab文字 | 阿里巴巴普惠体 | `tab-item` | 简洁 |
| 徽章文字 | 站酷高端黑 | `badge-gold` | 醒目 |

---

## 🔥 快速配置（最简方案）

### 只下载这2个字体：

1. **霞鹜文楷 Regular** (正文)
   - 下载: https://github.com/lxgw/LxgwWenKai/releases
   - 重命名: `XiaWuWenKai-Regular.ttf`

2. **站酷高端黑** (按钮)
   - 下载: https://www.zcool.com.cn/special/zcoolfonts/
   - 文件名: `ZhanKuGaoDuanHei.ttf`

### 放入目录：
```
src/static/ttf/XiaWuWenKai-Regular.ttf
src/static/ttf/ZhanKuGaoDuanHei.ttf
```

### 在 main.js 添加：
```javascript
import '@/styles/fonts.css'
```

**完成！立即拥有国风字体！**

---

## ⚠️ 注意事项

### 1. 字体文件大小
- 每个中文字体约 5-15MB
- 建议只使用必要的字体
- 快速方案（2个字体）约 15MB
- 完整方案（7个字体）约 70MB

### 2. 加载性能
- 使用 `font-display: swap` 避免白屏
- 首屏优先加载小文件
- 字体会被浏览器缓存

### 3. 字体授权
- 所有推荐字体均可免费商用
- 霞鹜文楷：SIL Open Font License
- 站酷系列：站酷免费商用授权
- 思源黑体：Apache License 2.0
- 阿里巴巴普惠体：免费商用

### 4. 降级方案
如果字体加载失败，会自动使用系统字体：
```css
'XiaWuWenKai', 'PingFang SC', 'Microsoft YaHei', sans-serif
```

---

## 🎯 效果预览

### 优化前
- 全部使用系统默认字体
- 按钮文字普通
- 缺乏国风韵味

### 优化后
- 正文：优雅的楷体，提升阅读体验
- 按钮：粗壮有力的黑体，视觉冲击力强
- 标题：现代大气，层次分明
- 整体：浓郁的国风氛围

---

## 📞 常见问题

### Q: 字体文件太大怎么办？
A: 使用快速方案，只下载2个必需字体

### Q: 字体不显示？
A: 检查文件路径和文件名是否正确匹配

### Q: 影响加载速度？
A: 字体会被缓存，首次加载后不影响

### Q: 小程序支持吗？
A: 支持，uni-app会自动处理

### Q: 可以用其他字体吗？
A: 可以！只需修改 `fonts.css` 中的字体名称

---

## 🚀 立即开始

1. 下载霞鹜文楷和站酷高端黑
2. 放入 `src/static/ttf/` 目录
3. 在 `main.js` 引入 `fonts.css`
4. 运行 `npm run dev:h5`
5. 享受国风字体！

**所有字体链接汇总：**
- https://github.com/lxgw/LxgwWenKai/releases
- https://www.zcool.com.cn/special/zcoolfonts/
- https://github.com/adobe-fonts/source-han-sans/releases
- https://fonts.alibabagroup.com/
- https://wangchujiang.com/free-font/
