# 快速部署指南

## ⏱️ 预计时间: 10分钟

### 步骤1: 上传代码 (2分钟)

```bash
# 在服务器上
cd /home/wwwroot
git clone <仓库地址> yixue
cd yixue
```

### 步骤2: 配置环境 (2分钟)

```bash
cd docker-deploy
cp .env.example .env
nano .env

# 修改这些密码:
DB_PASS=your_secure_password
DB_ROOT_PASS=your_root_password
REDIS_PASSWORD=your_redis_password
```

### 步骤3: 更新配置 (1分钟)

```bash
chmod +x update-config.sh
bash update-config.sh
```

### 步骤4: 启动服务 (5分钟)

```bash
chmod +x deploy.sh
bash deploy.sh
```

## ✅ 验证部署

```bash
# 1. 检查容器状态
docker-compose ps

# 2. 测试HTTP
curl http://localhost:8080

# 3. 测试数据库
docker exec yixue-mysql mysql -u yixue -p -e "SHOW DATABASES;"

# 4. 测试Redis
docker exec yixue-redis redis-cli -a redis2024 ping

# 5. 查看日志
docker-compose logs --tail=50
```

## 🌐 访问地址

### 直接访问
```
HTTP:  http://服务器IP:8080
HTTPS: https://服务器IP:8443
```

### 通过域名（需配置反向代理）
```
http://yixue.linqingkeji.com
```

## 🔧 常见问题

### Q: 端口被占用
```bash
# 检查端口占用
netstat -tulpn | grep -E '8080|8443|3307|6380'

# 修改docker-compose.yml中的端口
```

### Q: 数据库连接失败
```bash
# 检查容器状态
docker ps

# 查看数据库日志
docker-compose logs yixue-mysql

# 测试连接
docker exec yixue-mysql mysql -u yixue -p
```

### Q: 文件权限错误
```bash
# 修复权限
docker exec yixue-php chown -R www-data:www-data /var/www/yixue
chmod -R 755 ../1213-easy-to-learn/public/uploads
chmod -R 755 ../1213-easy-to-learn/runtime
```

## 📞 需要帮助?

查看详细文档: [README.md](README.md)
