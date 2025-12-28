# 1213易学 Docker 一键部署

## 快速开始

### 1. 配置环境变量
```bash
cp .env.example .env
nano .env  # 修改数据库密码等配置
```

### 2. 一键部署（包含H5）
```bash
bash deploy-all.sh
```

脚本会询问是否部署H5，选择 `y` 则自动在Docker容器中构建H5前端。

### 3. 仅部署后端
```bash
bash deploy.sh
```

### 4. 单独构建H5
```bash
bash build-h5.sh
```

## 服务访问

- **API**: http://服务器IP:8080
- **H5**: http://服务器IP:8081
- **MySQL**: 端口 3307
- **Redis**: 端口 6380

## Docker 部署说明

本项目使用 Docker Compose 进行容器化部署。

## 服务列表

- **nginx**: Web 服务器（配置ThinkPHP路由）
- **php**: PHP-FPM 7.4
- **mysql**: MySQL 5.7
- **redis**: Redis 5
- **h5**: H5 前端应用（uni-app + Vue3）
- **phpmyadmin**: 数据库管理工具

## 快速开始

```bash
# 启动所有服务
docker-compose up -d

# 查看服务状态
docker-compose ps

# 查看日志
docker-compose logs -f

# 停止所有服务
docker-compose down
```

## 端口映射

- Nginx: 8080 (HTTP), 8443 (HTTPS)
- MySQL: 3307
- Redis: 6380
- H5: 8081
- phpMyAdmin: 8082

## 数据持久化

所有数据存储在 `./data` 目录下：
- MySQL 数据: `./data/mysql`
- Redis 数据: `./data/redis`
- 日志文件: `./data/logs`

## Nginx配置说明

### ThinkPHP路由配置

Nginx配置文件位于 `./nginx/default.conf`，包含以下关键配置：

```nginx
location / {
    if (!-e $request_filename){
        rewrite  ^(.*)$  /index.php?s=$1  last;
    }
}
```

此配置实现：
- 将所有不存在的文件请求重写到 `index.php?s=路径`
- 支持ThinkPHP的URL路由
- 前端API调用无需添加 `index.php?s=` 前缀

### 修改Nginx配置后重新构建

```bash
# 重新构建Nginx镜像
docker-compose build yixue-nginx

# 重启Nginx容器
docker-compose up -d --force-recreate yixue-nginx
```

## H5构建与部署

### 构建H5应用

```bash
# 使用构建脚本（推荐）
bash build-h5.sh

# 或手动构建
docker run --rm -v $(pwd)/../1213yixuesuanming/1213yixuesuanming:/app -w /app node:18-alpine sh -c "npm install && npm run build:h5"
```

### 重启H5容器

```bash
docker-compose restart yixue-h5
```

## 常见问题

### API返回HTML而不是JSON

**原因**: Nginx rewrite规则未生效

**解决方案**:
1. 检查 `./nginx/default.conf` 中的rewrite规则
2. 重新构建Nginx镜像: `docker-compose build yixue-nginx`
3. 强制重新创建容器: `docker-compose up -d --force-recreate yixue-nginx`

### CORS跨域问题

**后端配置**: `application/config.php`
```php
'cors_request_domain' => '*',
```

**前端配置**: `src/config/website.js`
```javascript
const getBaseURL = () => {
  // #ifdef H5
  if (import.meta.env.DEV) return '';
  return 'http://1.12.230.141:8080';
  // #endif
}

## 常用命令

```bash
# 查看服务状态
docker-compose ps

# 查看日志
docker-compose logs -f

# 重启服务
docker-compose restart

# 停止服务
docker-compose down

# 进入容器
docker exec -it yixue-php bash
```

## 故障排查

遇到问题请查看：
- **DEPLOY-ISSUES.md** - 部署问题解决方案
- **TROUBLESHOOTING.md** - 完整故障排查指南

## 文件说明

- `deploy-all.sh` - 一键部署脚本（后端+H5）
- `deploy.sh` - 后端部署脚本
- `build-h5.sh` - H5构建脚本
- `update-config.sh` - 配置更新脚本
- `docker-compose.yml` - Docker编排文件
- `.env` - 环境变量配置

## 注意事项

1. 首次部署必须先执行 `bash update-config.sh` 修改数据库配置
2. H5构建需要Node.js 18+，脚本会自动在Docker容器中构建
3. 确保服务器开放 8080、8081、3307、6380 端口
4. 生产环境务必修改 `.env` 中的默认密码
