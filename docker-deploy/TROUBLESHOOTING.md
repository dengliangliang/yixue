# Docker部署故障排查指南

## H5构建相关问题

### 问题1: recyclableRender导出错误

**现象**:
```
Module parse failed: Export 'recyclableRender' is not defined
```

**原因**: 
- `@dcloudio/vue-cli-plugin-uni` 版本 `2.0.2-4080420251103001` 的已知bug
- 该版本是开发构建版本，包含日期戳，不稳定

**解决方案**:
```bash
# 推荐方案: 使用HBuilderX IDE构建
# 1. 下载HBuilderX: https://www.dcloud.io/hbuilderx.html
# 2. 导入项目: 文件 -> 导入 -> 从本地目录导入
# 3. 运行 -> 运行到浏览器 -> Chrome
# 4. 发行 -> 网站-H5手机版

# 临时方案: 等待官方修复
# 注意: latest标签仍然指向有问题的版本
npm install @dcloudio/vue-cli-plugin-uni@latest --legacy-peer-deps
```

**已尝试的无效方案**:
- ❌ 降级到稳定版本（latest仍是同一版本）
- ❌ 清理缓存重新安装
- ❌ 添加vue-loader到devDependencies
- ❌ 修改vue.config.js配置

---

### 问题2: 项目结构错误

**现象**:
```
Error: Cannot find module '@/common/util.js'
Error: Cannot find module './config/website'
```

**原因**:
- `common`、`config`、`uni.promisify.adaptor.js` 在项目根目录
- 但 `main.js` 使用 `@/common` 引用（`@` 指向 `src/`）

**解决方案**:
```bash
cd 1213yixuesuanming/1213yixuesuanming

# 移动目录到正确位置
mv common src/
mv config src/
mv uni.promisify.adaptor.js src/

# 验证结构
ls -la src/
# 应该看到: common/ config/ uni.promisify.adaptor.js
```

**自动修复**:
更新后的 `build-h5.sh` 脚本会自动检查和修复此问题

---

### 问题3: 依赖缺失错误

**现象**:
```
Error: Cannot find module 'cache-loader'
Error: Cannot find module 'thread-loader'
Error: Cannot find module 'file-loader'
Error: Cannot find module 'html-webpack-plugin'
Error: Cannot find module 'webpack'
```

**原因**:
- `package.json` 中缺少必要的 webpack loaders
- uni-app 构建需要这些依赖但未自动安装

**解决方案**:
```bash
# 方案1: 使用 --legacy-peer-deps 安装（推荐）
npm install --legacy-peer-deps

# 方案2: 手动添加缺失的依赖
npm install --save-dev \
  cache-loader@^4.1.0 \
  thread-loader@^2.1.3 \
  file-loader@^6.2.0 \
  url-loader@^4.1.1 \
  postcss-loader@^3.0.0 \
  html-webpack-plugin@^3.2.0 \
  webpack@^4.46.0 \
  copy-webpack-plugin@^5.1.2 \
  terser-webpack-plugin@^4.2.3

# 方案3: 清理后重新安装
rm -rf node_modules package-lock.json
npm install --legacy-peer-deps
```

**package.json 必需的 devDependencies**:
```json
{
  "devDependencies": {
    "@dcloudio/uni-cli-i18n": "latest",
    "@dcloudio/uni-cli-shared": "latest",
    "@dcloudio/uni-i18n": "latest",
    "@dcloudio/uni-migration": "latest",
    "@dcloudio/uni-template-compiler": "latest",
    "@dcloudio/vue-cli-plugin-uni": "latest",
    "@dcloudio/webpack-uni-mp-loader": "latest",
    "@dcloudio/webpack-uni-pages-loader": "latest",
    "@vue/cli-plugin-babel": "~4.5.0",
    "@vue/cli-service": "~4.5.0",
    "autoprefixer": "^9.8.8",
    "cache-loader": "^4.1.0",
    "copy-webpack-plugin": "^5.1.2",
    "cross-env": "^7.0.2",
    "file-loader": "^6.2.0",
    "html-webpack-plugin": "^3.2.0",
    "postcss": "^7.0.39",
    "postcss-loader": "^3.0.0",
    "sass": "^1.32.0",
    "sass-loader": "^10.1.1",
    "terser-webpack-plugin": "^4.2.3",
    "thread-loader": "^2.1.3",
    "url-loader": "^4.1.1",
    "vue-loader": "^15.9.8",
    "vue-template-compiler": "^2.6.11",
    "webpack": "^4.46.0"
  }
}
```

---

### 问题4: PostCSS版本冲突

**现象**:
```
PostCSS plugin autoprefixer requires PostCSS 8
```

**原因**:
- `autoprefixer` 和 `postcss` 版本不匹配
- `postcss-loader@3.x` 需要 `postcss@7.x`

**解决方案**:
```bash
# 降级到兼容版本
npm install --save-dev \
  autoprefixer@^9.8.8 \
  postcss@^7.0.39 \
  postcss-loader@^3.0.0
```

---

### 问题5: SCSS语法错误

**现象**:
```
SassError: expected selector
```

**原因**:
- 在 scoped 样式中直接使用元素选择器（如 `picker-view`）
- 需要使用 `::v-deep` 穿透作用域

**解决方案**:
```scss
/* 错误写法 */
<style lang="scss" scoped>
picker-view {
  /* ... */
}
</style>

/* 正确写法 */
<style lang="scss" scoped>
::v-deep picker-view {
  /* ... */
}
</style>
```

---

## Windows环境问题

### 问题6: PowerShell && 语法错误

**现象**:
```powershell
标记"&&"不是此版本中的有效语句分隔符
```

**原因**:
- PowerShell 不支持 `&&` 作为命令分隔符
- `&&` 是 Bash/Shell 语法

**解决方案**:
```powershell
# 方案1: 分开执行（推荐）
npm install --legacy-peer-deps
npm run build:h5

# 方案2: 使用分号
npm install --legacy-peer-deps; npm run build:h5

# 方案3: 使用 Git Bash
bash -c "npm install --legacy-peer-deps && npm run build:h5"

# 方案4: 使用 WSL
wsl bash build-h5.sh
```

---

## Docker部署问题

### 问题7: 容器无法访问

**现象**: 无法访问API或H5页面

**排查步骤**:
```bash
# 1. 检查容器状态
docker-compose ps

# 2. 检查端口占用
netstat -tulpn | grep -E '8080|8081|8443'

# 3. 检查防火墙
# CentOS/RHEL
firewall-cmd --list-all
firewall-cmd --permanent --add-port=8080/tcp
firewall-cmd --permanent --add-port=8081/tcp
firewall-cmd --reload

# Ubuntu/Debian
ufw status
ufw allow 8080/tcp
ufw allow 8081/tcp

# 4. 查看容器日志
docker-compose logs yixue-nginx
docker-compose logs yixue-h5
```

---

### 问题8: 数据库连接失败

**现象**: API返回500错误，日志显示数据库连接失败

**排查步骤**:
```bash
# 1. 检查数据库容器
docker-compose ps yixue-db
docker logs yixue-db

# 2. 测试数据库连接
docker exec yixue-db mysql -u root -p${DB_ROOT_PASS} -e "SHOW DATABASES;"

# 3. 检查配置文件
grep -n "host" ../1213-easy-to-learn/application/database.php

# 4. 验证网络连通性
docker exec yixue-php ping yixue-db
```

---

## 性能优化

### PHP-FPM调优

编辑 `php/php-fpm.conf`:
```ini
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 2048
```

### Nginx调优

编辑 `nginx/nginx.conf`:
```nginx
worker_processes auto;
worker_connections 2048;
keepalive_timeout 65;
client_max_body_size 50M;
```

---

## 日志查看

```bash
# 查看所有容器日志
docker-compose logs

# 查看特定容器日志
docker-compose logs yixue-nginx
docker-compose logs yixue-php
docker-compose logs yixue-h5

# 实时跟踪日志
docker-compose logs -f yixue-nginx

# 查看最近N行
docker-compose logs --tail=100 yixue-php

# 查看容器内应用日志
docker exec yixue-php tail -f /var/www/html/runtime/log/error.log
```

---

## 紧急回滚

如果部署出现严重问题：

```bash
# 1. 停止新容器
docker-compose down

# 2. 恢复数据库备份
docker exec -i yixue-db mysql -u root -p${DB_ROOT_PASS} yixue < backup/yixue_backup.sql

# 3. 重新部署旧版本
git checkout <previous-commit>
docker-compose up -d

# 4. 验证服务
curl http://localhost:8080/api/test
```
