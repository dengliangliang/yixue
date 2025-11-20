# Docker部署问题记录与解决方案

本文档记录了实际部署过程中遇到的问题和解决方案，供后续部署参考。

## 问题1: Composer未安装导致依赖缺失

### 现象
```
HTTP 500错误
PHP Fatal error: Class 'think\App' not found
```

### 原因
- PHP容器中未安装 Composer
- 项目依赖（vendor目录）未安装

### 解决方案

**方法1: 在宿主机安装Composer并安装依赖**
```bash
# 1. 在宿主机安装Composer
cd /home/wwwroot/yixue/1213-easy-to-learn
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# 2. 安装项目依赖
composer install --no-dev --optimize-autoloader

# 3. 设置权限
chown -R www:www vendor/
chmod -R 755 vendor/
```

**方法2: 在PHP容器内安装（推荐）**
```bash
# 1. 进入PHP容器
docker exec -it yixue-php bash

# 2. 安装Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# 3. 进入项目目录
cd /var/www/yixue

# 4. 安装依赖
composer install --no-dev --optimize-autoloader

# 5. 退出容器
exit

# 6. 在宿主机设置权限
docker exec yixue-php chown -R www-data:www-data /var/www/yixue/vendor
```

**方法3: 修改Dockerfile自动安装（最佳）**

在 `php/Dockerfile` 中添加：
```dockerfile
# 安装Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

# 复制composer文件
COPY ../1213-easy-to-learn/composer.json /var/www/yixue/
COPY ../1213-easy-to-learn/composer.lock /var/www/yixue/

# 安装依赖
WORKDIR /var/www/yixue
RUN composer install --no-dev --optimize-autoloader --no-scripts

# 设置权限
RUN chown -R www-data:www-data /var/www/yixue
```

---

## 问题2: 数据库连接失败

### 现象
```
SQLSTATE[HY000] [2002] Connection refused
或
SQLSTATE[HY000] [2002] php_network_getaddresses: getaddrinfo failed: Name or service not known
```

### 原因
- 配置文件中数据库host仍为 `127.0.0.1`
- Docker环境需要使用容器名 `yixue-mysql`

### 解决方案

**必须修改的配置文件**:

`1213-easy-to-learn/application/database.php`:
```php
return [
    // 数据库类型
    'type'            => 'mysql',
    // 服务器地址
    'hostname'        => 'yixue-mysql',  // ← 改为容器名
    // 数据库名
    'database'        => 'yixue',
    // 用户名
    'username'        => 'root',
    // 密码
    'password'        => getenv('DB_ROOT_PASS') ?: 'your_password',
    // 端口
    'port'            => '3306',
    // 数据库编码
    'charset'         => 'utf8mb4',
    // 数据库表前缀
    'prefix'          => '',
];
```

**自动化脚本**:
```bash
# 创建配置修改脚本
cat > update-db-config.sh << 'EOF'
#!/bin/bash
set -e

echo "🔧 修改数据库配置..."

# 修改database.php
sed -i "s/'hostname'.*=>.*'127.0.0.1'/'hostname' => 'yixue-mysql'/g" \
    ../1213-easy-to-learn/application/database.php

echo "✅ 数据库配置已更新为Docker容器名"
EOF

chmod +x update-db-config.sh
```

---

## 问题3: Redis连接失败

### 现象
```
Redis connection refused
或
Can't connect to Redis server
```

### 原因
- Redis配置中host为 `127.0.0.1`
- 需要改为容器名 `yixue-redis`

### 解决方案

检查并修改所有Redis连接配置：

```bash
# 搜索所有Redis配置
grep -r "127.0.0.1.*6379" ../1213-easy-to-learn/

# 修改为容器名
sed -i "s/127.0.0.1.*6379/yixue-redis:6379/g" \
    ../1213-easy-to-learn/application/config.php
```

---

## 问题4: 文件上传权限错误

### 现象
```
Permission denied
或
Failed to write file
```

### 原因
- 上传目录权限不正确
- Docker容器内www-data用户无写权限

### 解决方案

```bash
# 方法1: 在宿主机设置权限
cd /home/wwwroot/yixue/1213-easy-to-learn
chown -R www:www public/uploads
chmod -R 755 public/uploads

# 方法2: 在容器内设置权限
docker exec yixue-php chown -R www-data:www-data /var/www/yixue/public/uploads
docker exec yixue-php chmod -R 755 /var/www/yixue/public/uploads

# 方法3: 设置runtime目录权限
docker exec yixue-php chown -R www-data:www-data /var/www/yixue/runtime
docker exec yixue-php chmod -R 755 /var/www/yixue/runtime
```

---

## 问题5: MySQL容器反复重启

### 现象
```bash
docker ps
# 显示: yixue-mysql   Restarting (1) 45 seconds ago
```

### 原因
- 数据库初始化失败
- init.sql文件格式问题
- 内存不足

### 解决方案

```bash
# 1. 查看MySQL日志
docker logs yixue-mysql

# 2. 检查init.sql文件
ls -lh mysql/init.sql
head -20 mysql/init.sql

# 3. 手动导入数据库
# 先停止容器
docker-compose down

# 删除数据目录（注意备份！）
rm -rf data/mysql/*

# 重新启动
docker-compose up -d yixue-mysql

# 等待MySQL启动完成
sleep 30

# 手动导入
docker exec -i yixue-mysql mysql -u root -p${DB_ROOT_PASS} yixue < mysql/init.sql

# 4. 验证导入
docker exec yixue-mysql mysql -u root -p${DB_ROOT_PASS} -e "USE yixue; SHOW TABLES;"
```

---

## 问题6: Nginx 502 Bad Gateway

### 现象
访问网站显示 502 错误

### 原因
- PHP-FPM容器未启动
- PHP-FPM配置错误
- Nginx无法连接到PHP容器

### 解决方案

```bash
# 1. 检查PHP容器状态
docker-compose ps yixue-php

# 2. 查看PHP容器日志
docker-compose logs yixue-php

# 3. 检查Nginx配置
docker exec yixue-nginx nginx -t

# 4. 检查网络连通性
docker exec yixue-nginx ping yixue-php

# 5. 重启服务
docker-compose restart yixue-nginx yixue-php
```

---

## 问题7: 环境变量未生效

### 现象
- 数据库密码错误
- Redis密码错误
- 配置值为空

### 原因
- .env文件未创建
- .env文件格式错误
- 容器未重启

### 解决方案

```bash
# 1. 检查.env文件
cat .env

# 2. 确保格式正确（无空格）
DB_ROOT_PASS=your_password
DB_NAME=yixue
REDIS_PASSWORD=your_redis_pass

# 3. 重启容器使环境变量生效
docker-compose down
docker-compose up -d

# 4. 验证环境变量
docker exec yixue-php env | grep DB_
```

---

## 部署检查清单

### 部署前检查
- [ ] Docker和Docker Compose已安装
- [ ] .env文件已配置
- [ ] 数据库备份文件已准备（mysql/init.sql）
- [ ] 上传文件已同步
- [ ] SSL证书已放置

### 配置修改检查
- [ ] database.php中hostname改为yixue-mysql
- [ ] Redis配置改为yixue-redis
- [ ] 文件上传路径正确
- [ ] 日志目录权限正确

### 部署后检查
- [ ] 所有容器正常运行（docker-compose ps）
- [ ] 数据库连接成功
- [ ] Redis连接成功
- [ ] 网站可以访问
- [ ] 文件上传功能正常
- [ ] 日志正常写入

---

## 快速故障排查命令

```bash
# 查看所有容器状态
docker-compose ps

# 查看容器日志
docker-compose logs -f yixue-nginx
docker-compose logs -f yixue-php
docker-compose logs -f yixue-mysql
docker-compose logs -f yixue-redis

# 进入容器调试
docker exec -it yixue-php bash
docker exec -it yixue-nginx bash
docker exec -it yixue-mysql bash

# 测试数据库连接
docker exec yixue-mysql mysql -u root -p${DB_ROOT_PASS} -e "SHOW DATABASES;"

# 测试Redis连接
docker exec yixue-redis redis-cli -a ${REDIS_PASSWORD} ping

# 检查文件权限
docker exec yixue-php ls -la /var/www/yixue/public/uploads
docker exec yixue-php ls -la /var/www/yixue/runtime

# 重启所有服务
docker-compose restart

# 完全重建
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

---

## 性能优化建议

### PHP优化
```ini
# php/php.ini
memory_limit = 256M
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
```

### MySQL优化
```ini
# mysql/my.cnf
[mysqld]
innodb_buffer_pool_size = 512M
max_connections = 200
query_cache_size = 64M
```

### Nginx优化
```nginx
# nginx/nginx.conf
worker_processes auto;
worker_connections 2048;
keepalive_timeout 65;
client_max_body_size 50M;
```
