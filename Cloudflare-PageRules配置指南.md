# Cloudflare Page Rules 配置指南

## 📋 配置目标
让静态资源走CDN加速，API请求绕过CDN直连服务器

---

## 🔧 Cloudflare Dashboard 配置步骤

### 登录Cloudflare
1. 访问 https://dash.cloudflare.com/
2. 选择域名：**yixue.linqingkeji.com**

---

### 第一步：配置 Page Rules

进入：**规则（Rules）** → **页面规则（Page Rules）**

#### **规则 1：API 绕过 CDN缓存**

```
URL Pattern: *yixue.linqingkeji.com/api/*

Settings（设置）:
✓ Cache Level: Bypass (绕过)
✓ Disable Apps (禁用应用)
✓ Disable Performance (禁用性能优化)

优先级：1 (最高)
```

**点击保存**

#### **规则 2：静态资源强缓存**

```
URL Pattern: *yixue.linqingkeji.com/static/*

Settings（设置）:
✓ Cache Level: Cache Everything (缓存所有内容)
✓ Edge Cache TTL: 1 month (1个月)
✓ Browser Cache TTL: 1 month (1个月)

优先级：2
```

**点击保存**

#### **规则 3：H5静态文件缓存**

```
URL Pattern: *yixue.linqingkeji.com/*.js

Settings（设置）:
✓ Cache Level: Cache Everything
✓ Edge Cache TTL: 1 week
✓ Browser Cache TTL: 1 week

优先级：3
```

**点击保存**

#### **规则 4：CSS/图片缓存**

```
URL Pattern: *yixue.linqingkeji.com/*.css

Settings（设置）:
✓ Cache Level: Cache Everything
✓ Edge Cache TTL: 1 week
✓ Browser Cache TTL: 1 week

优先级：4
```

**点击保存**

---

### 第二步：Speed 优化设置

进入：**速度（Speed）** → **优化（Optimization）**

#### **Auto Minify（自动压缩）**
```
✓ JavaScript
✓ CSS  
✓ HTML
```

#### **Brotli 压缩**
```
✓ 启用 Brotli
```

#### **Rocket Loader（可选）**
```
✗ 不启用（可能与uni-app冲突）
```

---

### 第三步：Caching 缓存设置

进入：**缓存（Caching）** → **配置（Configuration）**

#### **Caching Level（缓存级别）**
```
选择：Standard (标准)
```

#### **Browser Cache TTL**
```
选择：Respect Existing Headers (尊重现有头)
```

#### **Always Online（始终在线）**
```
✓ 启用
```

---

### 第四步：Network 网络设置

进入：**网络（Network）**

#### **HTTP/2**
```
✓ 启用（默认已启用）
```

#### **HTTP/3 (QUIC)**
```
✓ 启用
```

#### **WebSockets**
```
✓ 启用
```

#### **0-RTT Connection Resumption**
```
✓ 启用（加快连接速度）
```

---

## 📊 配置后效果验证

### 1. 检查API是否绕过CDN

```bash
# 方法1：使用curl查看响应头
curl -I https://yixue.linqingkeji.com/api/user/getProvinceList

# 查看响应头，应该包含：
# Cache-Control: no-store, no-cache
# X-Cache-Status: BYPASS
# cf-cache-status: DYNAMIC 或 BYPASS
```

### 2. 检查静态资源是否命中CDN

```bash
# 查看静态文件
curl -I https://yixue.linqingkeji.com/static/beijing.jpg

# 响应头应包含：
# Cache-Control: public, max-age=2592000
# cf-cache-status: HIT (第二次请求时)
# age: xxx (CDN缓存时间)
```

### 3. 浏览器DevTools验证

**打开 Chrome DevTools** → **Network**

- **API请求**: `cf-cache-status: DYNAMIC` 或 `BYPASS`
- **静态资源**: `cf-cache-status: HIT` (命中缓存)

---

## ⚡ 预期性能提升

### 优化前
```
API请求: 
客户端 → Cloudflare CDN (200ms) → 服务器 (2400ms) → CDN → 客户端
总延迟: ~3600ms
```

### 优化后
```
API请求:
客户端 → Cloudflare (BYPASS) → 服务器 (800ms优化后) → 客户端
总延迟: ~1000ms (快3.6倍)

静态资源:
客户端 → Cloudflare CDN (命中缓存 ~50ms) → 客户端
总延迟: ~50ms (快10-20倍)
```

---

## 🔍 常见问题排查

### Q1: API请求还是被CDN缓存了？
**检查：**
```bash
curl -I https://yixue.linqingkeji.com/api/user/getProvinceList
```
如果 `cf-cache-status: HIT`，说明配置未生效。

**解决：**
1. 检查 Page Rule 优先级（API规则应该是优先级1）
2. 清除Cloudflare缓存：Dashboard → Caching → Purge Everything
3. 等待5分钟让配置生效

### Q2: 静态资源没有被CDN缓存？
**检查：**
- 第一次访问：`cf-cache-status: MISS`（正常）
- 第二次访问：`cf-cache-status: HIT`（应该命中）

**解决：**
1. 确认 Nginx 响应头包含 `Cache-Control: public, max-age=xxx`
2. 确认 Page Rule 已设置 `Cache Level: Cache Everything`
3. 刷新页面2-3次等待CDN缓存

### Q3: 如何清除特定文件的CDN缓存？
**Dashboard** → **Caching** → **Custom Purge**
```
输入URL：
https://yixue.linqingkeji.com/static/beijing.jpg
```
点击 **Purge**

---

## 📌 Nginx配置已完成

✅ **H5 Nginx（容器 yixue-h5）**
- API: 禁止缓存 `Cache-Control: no-store`
- 静态资源: 强缓存 `max-age=2592000` (30天)

✅ **主Nginx（容器 yixue-nginx）**
- PHP响应: 禁止缓存 `Cache-Control: no-store`
- 静态文件: 强缓存 `max-age=2592000`

✅ **MySQL**
- Buffer Pool: 128M → 512M (快4倍)
- Query Cache: 32M → 64M
- 表缓存: 优化

---

## ✅ 当前已完成的优化

1. ✅ **去除gossip动画** - 节省15秒
2. ✅ **MySQL配置优化** - 查询快3-5倍
3. ✅ **Nginx响应头优化** - API禁止缓存
4. ⏳ **前端缓存逻辑** - 需修改setInfo.vue (编码问题未完成)
5. ⏳ **Cloudflare Page Rules** - 需要您在Dashboard手动配置

---

## 🎯 下一步

### 立即完成
1. **登录Cloudflare Dashboard** 按上述步骤配置 Page Rules（10分钟）

### 前端代码
2. **修改 setInfo.vue** - 手动修改或使用VSCode替换：
   - 搜索：`this.triggerBackgroundCalc(data.record_id);`
   - 替换为：`await this.calcAndCache(data.record_id);`
   - 添加 `calcAndCache` 方法（见gossip.vue已完成的实现）

### 测试验证
3. **刷新H5测试** - 查看性能是否提升
4. **查看Chrome DevTools** - 验证CDN配置

---

**预期结果：页面加载从38秒降至5-8秒** 
