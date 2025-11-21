# H5构建与部署

## 概述

本文档记录易学小程序H5版本的构建、部署流程和常见问题解决方案。

## 技术栈

- **框架**: uni-app 3.x + Vue 3 + Vite
- **UI库**: uview-plus
- **后端**: ThinkPHP 5
- **部署**: Docker + Nginx

## 构建流程

### 1. 本地开发

```bash
cd 1213yixuesuanming/1213yixuesuanming
npm install
npm run dev:h5
```

### 2. 生产构建

```bash
npm run build:h5
```

构建产物位于 `dist/build/h5/` 目录。

### 3. 服务器部署

使用Docker部署脚本：

```bash
cd docker-deploy
bash build-h5.sh
docker-compose restart yixue-h5
```

## 关键配置

### Nginx路由配置

**文件**: `docker-deploy/nginx/default.conf`

```nginx
location / {
    if (!-e $request_filename){
        rewrite  ^(.*)$  /index.php?s=$1  last;
    }
}
```

**作用**:
- 将前端路由请求重写到ThinkPHP
- 支持 `/api/user/getProvinceList` 自动转换为 `/index.php?s=/api/user/getProvinceList`

### CORS配置

**后端**: `1213-easy-to-learn/application/config.php`

```php
'cors_request_domain' => '*',
```

**前端**: `src/config/website.js`

```javascript
const getBaseURL = () => {
  // #ifdef H5
  if (import.meta.env.DEV) return ''; // 开发环境使用Vite代理
  return 'http://1.12.230.141:8080'; // 生产环境直接访问
  // #endif
}
```

### Vite代理配置

**文件**: `vite.config.js`

```javascript
server: {
  proxy: {
    '/api': {
      target: 'http://1.12.230.141:8080',
      changeOrigin: true,
      rewrite: path => path
    }
  }
}
```

## 已解决的问题

### 1. CommonJS模块兼容性

**问题**: H5环境不支持 `require` 和 `module.exports`

**解决方案**: 将所有CommonJS模块改为ES6模块

```javascript
// 旧代码
module.exports.AMapWX = AMapWX;

// 新代码
export default AMapWX;
export { AMapWX };
```

### 2. 全局方法未定义

**问题**: `this.$toast`, `this.$go` 等方法未定义

**解决方案**: 在 `main.js` 中挂载到Vue全局属性

```javascript
app.config.globalProperties.$toast = (title) => uni.showToast({
  title,
  icon: "none"
});

app.config.globalProperties.$go = (href, type = 0, time = 300) => uni.navigateTo({
  url: href,
  animationDuration: time,
  animationType: type == 0 ? "slide-in-right" : "zoom-fade-out"
});
```

### 3. API参数验证

**问题**: H5独立使用时缺少 `customerNo` 等参数

**解决方案**: 修改后端API，使这些参数变为可选

```php
// 只验证必需的参数
if (empty($date) || empty($area_id)) $this->error('参数不能为空');

// 如果customerNo为空，生成默认值
if (empty($customerNo)) {
    $customerNo = 'H5_' . uniqid();
}

// 只有当sign存在时才验签
if (!empty($sign)) {
    // 验签逻辑
}
```

### 4. Nginx路由问题

**问题**: API请求返回HTML而不是JSON

**原因**: Nginx未正确配置ThinkPHP路由

**解决方案**: 使用 `rewrite` 规则而不是 `try_files`

```nginx
# 错误配置
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

# 正确配置
location / {
    if (!-e $request_filename){
        rewrite  ^(.*)$  /index.php?s=$1  last;
    }
}
```

## 部署检查清单

- [ ] 后端代码已更新
- [ ] Nginx配置已修改并重新构建
- [ ] H5代码已构建
- [ ] H5容器已重启
- [ ] 浏览器缓存已清除
- [ ] API测试通过
- [ ] 前端功能测试通过

## 测试命令

### 测试API路由

```bash
# 测试省份列表API
curl -X POST "http://1.12.230.141:8080/api/user/getProvinceList" \
  -H "Origin: http://1.12.230.141:8081" \
  -H "Content-Type: application/json"

# 应该返回JSON数据，包含Access-Control-Allow-Origin头
```

### 测试H5访问

1. 访问 http://1.12.230.141:8081
2. 点击"立即解锁人生剧本"
3. 选择生日、出生地、性别
4. 点击"立即获取"
5. 查看结果页面

## 相关文档

- [Docker部署说明](../../../docker-deploy/README.md)
- [Nginx配置](../../../docker-deploy/nginx/default.conf)
- [API文档](../../database/database-design.md)
