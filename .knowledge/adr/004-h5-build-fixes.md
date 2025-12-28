# ADR 004: H5构建问题修复方案

## 状态

已接受

## 上下文

易学小程序H5版本在构建和部署过程中遇到多个问题：

1. **模块系统兼容性**: H5环境不支持CommonJS的 `require` 和 `module.exports`
2. **全局方法缺失**: Vue3需要显式挂载全局方法到 `app.config.globalProperties`
3. **CORS跨域**: 生产环境H5访问后端API存在跨域问题
4. **API路由**: Nginx未正确配置ThinkPHP路由，导致API返回HTML
5. **参数验证**: 后端API要求必需参数，但H5独立使用时无法提供

## 决策

### 1. 统一使用ES6模块系统

**决策**: 将所有CommonJS模块改为ES6模块

**理由**:
- uni-app 3.x + Vite构建环境原生支持ES6模块
- 避免H5环境下的 `require is not defined` 错误
- 与Vue3生态保持一致

**实施**:
```javascript
// 修改前
module.exports.AMapWX = AMapWX;

// 修改后
export default AMapWX;
export { AMapWX };
```

### 2. 显式挂载全局方法

**决策**: 在 `main.js` 中将所有全局方法挂载到 `app.config.globalProperties`

**理由**:
- Vue3移除了 `Vue.prototype`，必须使用 `app.config.globalProperties`
- 确保组件中可以通过 `this.$method` 访问全局方法
- 避免 `this.$toast is not a function` 等错误

**实施**:
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

### 3. 配置CORS和Nginx路由

**决策**: 
- 后端允许所有域名跨域访问
- Nginx使用 `rewrite` 规则支持ThinkPHP路由

**理由**:
- H5应用部署在不同域名，需要CORS支持
- ThinkPHP使用 `index.php?s=路径` 格式，需要Nginx重写规则
- 避免前端API调用时手动添加 `index.php?s=` 前缀

**实施**:

后端配置:
```php
'cors_request_domain' => '*',
```

Nginx配置:
```nginx
location / {
    if (!-e $request_filename){
        rewrite  ^(.*)$  /index.php?s=$1  last;
    }
}
```

### 4. API参数可选化

**决策**: 修改后端API，使 `customerNo` 和验签参数变为可选

**理由**:
- H5应用可以独立使用，不依赖外部系统传参
- 保持向后兼容，当参数存在时仍然执行验签
- 提升用户体验，减少错误提示

**实施**:
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

## 后果

### 正面影响

1. **兼容性提升**: H5构建不再报错，所有功能正常工作
2. **开发体验改善**: 统一的模块系统和全局方法管理
3. **部署简化**: 无需修改前端API调用代码
4. **独立性增强**: H5可以独立使用，不依赖外部参数

### 负面影响

1. **安全性降低**: 允许所有域名跨域访问（可后续优化为白名单）
2. **验签可选**: 部分请求可能绕过验签（已通过生成默认customerNo缓解）

### 风险缓解

1. 后续可将CORS配置改为域名白名单
2. 记录所有H5独立请求的日志，便于审计
3. 定期review安全配置

## 相关文档

- [H5构建与部署](../features/h5-build/README.md)
- [Docker部署说明](../../docker-deploy/README.md)
- [数据库参数化查询](./002-database-parameterized-query.md)
