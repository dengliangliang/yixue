# 1213易学项目 Docker部署方案

## 📋 服务清单

### 核心服务
- **yixue-nginx**: Web服务器 (8080, 8443端口)
- **yixue-php**: PHP 7.4-FPM应用服务
- **yixue-mysql**: MySQL 5.7数据库 (3307端口)
- **yixue-redis**: Redis 7.2缓存 (6380端口)

### 端口分配（避免冲突）
```
8080  → HTTP (不冲突宿主机80)
8443  → HTTPS (不冲突宿主机443)
3307  → MySQL (不冲突宿主机3306)
6380  → Redis (不冲突宿主机6379)
```

## 🚀 快速部署

### 步骤1: 上传代码到服务器
```bash
# 在服务器上
cd /home/wwwroot
git clone <仓库地址> yixue
cd yixue
```

### 步骤2: 配置环境变量
```bash
cd docker-deploy
cp .env.example .env
nano .env

# 修改密码:
# - DB_PASS
# - DB_ROOT_PASS
# - REDIS_PASSWORD
```

### 步骤3: 启动服务
```bash
chmod +x deploy.sh
bash deploy.sh
```

### 步骤4: 验证部署
```bash
# 测试HTTP
curl http://localhost:8080

# 测试数据库
docker exec yixue-mysql mysql -u yixue -p -e "SHOW DATABASES;"

# 测试Redis
docker exec yixue-redis redis-cli -a redis2024 ping

# 查看日志
docker-compose logs -f
```

## 🔧 配置修改

### 1. 数据库连接配置

修改 `application/database.php`:
```php
'hostname' => 'yixue-mysql',  // Docker容器名
'database' => 'yixue',
'username' => 'yixue',
'password' => 'yixue2024',
```

### 2. Redis连接配置

修改 `application/extra/queue.php`:
```php
'host'     => 'yixue-redis',  // Docker容器名
'port'     => 6379,
'password' => 'redis2024',
```

### 3. 使用环境变量（推荐）

修改 `application/database.php`:
```php
'hostname' => getenv('DB_HOST') ?: 'yixue-mysql',
'database' => getenv('DB_NAME') ?: 'yixue',
'username' => getenv('DB_USER') ?: 'yixue',
'password' => getenv('DB_PASS') ?: 'yixue2024',
```

## 📁 目录结构

```
docker-deploy/
├── docker-compose.yml       # Docker编排文件
├── .env                     # 环境变量
├── deploy.sh                # 部署脚本
├── nginx/                   # Nginx配置
│   ├── Dockerfile
│   ├── nginx.conf
│   └── default.conf
├── php/                     # PHP配置
│   ├── Dockerfile
│   ├── php.ini
│   └── php-fpm.conf
├── mysql/                   # MySQL配置
│   └── my.cnf
├── redis/                   # Redis配置
│   └── redis.conf
└── data/                    # 持久化数据
    ├── mysql/
    ├── redis/
    ├── logs/
    └── ssl/
```

## 🔍 常用命令

### 服务管理
```bash
# 启动所有服务
docker-compose up -d

# 停止所有服务
docker-compose down

# 重启服务
docker-compose restart yixue-nginx
docker-compose restart yixue-php

# 查看服务状态
docker-compose ps

# 查看日志
docker-compose logs -f yixue-php
docker-compose logs --tail=50
```

### 数据库操作
```bash
# 进入MySQL
docker exec -it yixue-mysql mysql -u yixue -p

# 导入SQL
docker exec -i yixue-mysql mysql -u yixue -p yixue < backup.sql

# 导出SQL
docker exec yixue-mysql mysqldump -u yixue -p yixue > backup.sql

# 查看数据库
docker exec yixue-mysql mysql -u yixue -p -e "SHOW DATABASES;"
```

### Redis操作
```bash
# 进入Redis
docker exec -it yixue-redis redis-cli -a redis2024

# 测试连接
docker exec yixue-redis redis-cli -a redis2024 ping

# 查看键
docker exec yixue-redis redis-cli -a redis2024 keys "*"

# 清空缓存
docker exec yixue-redis redis-cli -a redis2024 FLUSHALL
```

### PHP操作
```bash
# 进入PHP容器
docker exec -it yixue-php bash

# 执行ThinkPHP命令
docker exec yixue-php php /var/www/yixue/think

# 查看PHP版本
docker exec yixue-php php -v

# 查看PHP扩展
docker exec yixue-php php -m

# 修复权限
docker exec yixue-php chown -R www-data:www-data /var/www/yixue/runtime
docker exec yixue-php chown -R www-data:www-data /var/www/yixue/public/uploads
```

## 🌐 域名配置

### 方案A: 直接访问
```
http://服务器IP:8080
https://服务器IP:8443
```

### 方案B: 反向代理（推荐）

在宿主机Nginx添加配置:
```nginx
# /usr/local/nginx/conf/vhost/yixue.conf
server {
    listen 80;
    server_name yixue.linqingkeji.com;
    
    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}

server {
    listen 443 ssl;
    server_name yixue.linqingkeji.com;
    
    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;
    
    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

重载Nginx:
```bash
nginx -t
nginx -s reload
```

## 🛡️ 安全建议

1. **修改默认密码**: 修改.env中的所有密码
2. **防火墙配置**: 只开放必要端口
3. **定期备份**: 备份数据库和上传文件
4. **日志监控**: 定期查看错误日志
5. **更新镜像**: 定期更新Docker镜像

## 📊 资源占用

```
yixue-nginx:  ~50MB
yixue-php:    ~200MB
yixue-mysql:  ~300MB
yixue-redis:  ~50MB
─────────────────────
总计:         ~600MB
```

## 🔧 故障排查

### 容器无法启动
```bash
# 查看详细日志
docker-compose logs yixue-php

# 检查配置
docker-compose config

# 重新构建
docker-compose build --no-cache
docker-compose up -d
```

### 数据库连接失败
```bash
# 检查容器状态
docker ps

# 检查数据库密码
cat .env | grep DB_

# 测试连接
docker exec yixue-mysql mysql -u yixue -p -e "SELECT 1;"
```

### 文件权限错误
```bash
# 修复权限
docker exec yixue-php chown -R www-data:www-data /var/www/yixue
chmod -R 755 ../1213-easy-to-learn/public/uploads
chmod -R 755 ../1213-easy-to-learn/runtime
```

### Redis连接失败
```bash
# 检查Redis状态
docker exec yixue-redis redis-cli -a redis2024 ping

# 检查密码
cat .env | grep REDIS_

# 查看Redis日志
docker-compose logs yixue-redis
```

## 📝 注意事项

1. **端口冲突**: 确保8080/8443/3307/6380端口未被占用
2. **数据持久化**: data目录包含所有数据，注意备份
3. **配置修改**: 修改配置后需要重启容器
4. **日志清理**: 定期清理日志文件
5. **资源监控**: 监控容器资源使用情况

## 📞 技术支持

如遇问题，请检查:
1. Docker和Docker Compose版本
2. 服务器资源（CPU、内存、磁盘）
3. 端口是否被占用
4. 日志文件中的错误信息
