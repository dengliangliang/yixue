# 部署检查清单

## 📋 部署前准备

### 服务器环境
- [ ] Docker已安装 (`docker --version`)
- [ ] Docker Compose已安装 (`docker-compose --version`)
- [ ] 端口未被占用 (8080, 8443, 3307, 6380, 8081)
- [ ] 磁盘空间充足 (至少2GB)
- [ ] 内存充足 (建议2GB+)

### 代码准备
- [ ] 代码已上传到服务器 `/home/wwwroot/yixue`
- [ ] yixue.sql文件存在 `/home/wwwroot/yixue/yixue.sql`
- [ ] docker-deploy目录完整

---

## 🔧 配置检查

### 环境变量
- [ ] .env文件已创建
- [ ] DB_PASS已设置（当前: JCpYZwjxmCDdHcCF）
- [ ] DB_ROOT_PASS已设置
- [ ] REDIS_PASSWORD已设置

### 应用配置
- [ ] update-config.sh已执行
- [ ] database.php已更新为容器配置
- [ ] queue.php已更新为容器配置

### H5配置（如需部署）
- [ ] website.js中API地址已配置
- [ ] H5已构建 (build-h5.sh)
- [ ] 构建文件存在 h5/dist/

---

## 🚀 部署执行

### 后端API
- [ ] 执行 `bash deploy.sh`
- [ ] 容器启动成功
- [ ] 数据库已导入
- [ ] API可访问 (curl http://localhost:8080)

### H5前端（可选）
- [ ] 执行 `bash build-h5.sh`
- [ ] 执行 `docker-compose up -d yixue-h5`
- [ ] H5可访问 (curl http://localhost:8081)

---

## ✅ 功能验证

### 后端API
```bash
# 1. 检查容器状态
docker-compose ps
# 应该看到: yixue-nginx, yixue-php, yixue-mysql, yixue-redis (Up状态)

# 2. 测试HTTP
curl http://localhost:8080
# 应该返回HTML或JSON

# 3. 测试数据库
docker exec yixue-mysql mysql -u yixue -pJCpYZwjxmCDdHcCF -e "SHOW DATABASES;"
# 应该看到yixue数据库

# 4. 测试Redis
docker exec yixue-redis redis-cli -a redis密码 ping
# 应该返回PONG

# 5. 查看日志
docker-compose logs --tail=50
# 检查是否有错误
```

### H5前端
```bash
# 1. 检查容器
docker ps | grep yixue-h5

# 2. 测试访问
curl http://localhost:8081

# 3. 浏览器测试
# 打开 http://服务器IP:8081
# 检查页面是否正常显示
# 检查API调用是否成功
```

---

## 🌐 域名配置（可选）

### API域名
- [ ] DNS已解析 api.yixue.linqingkeji.com
- [ ] 宿主机Nginx已配置反向代理
- [ ] SSL证书已配置（如需HTTPS）
- [ ] 可通过域名访问

### H5域名
- [ ] DNS已解析 h5.yixue.linqingkeji.com
- [ ] 宿主机Nginx已配置反向代理
- [ ] 可通过域名访问

---

## 🔒 安全检查

### 密码安全
- [ ] 数据库密码已修改（不使用默认）
- [ ] Redis密码已设置
- [ ] Root密码已修改

### 防火墙
- [ ] 只开放必要端口 (80, 443, 8080, 8443, 8081)
- [ ] 数据库端口3307仅内网访问
- [ ] Redis端口6380仅内网访问

### 文件权限
- [ ] uploads目录可写 (755)
- [ ] runtime目录可写 (755)
- [ ] data目录权限正确

---

## 📊 监控检查

### 资源使用
```bash
# 查看容器资源
docker stats

# 查看磁盘使用
df -h

# 查看内存使用
free -h
```

### 日志监控
```bash
# 实时查看日志
docker-compose logs -f

# 查看错误日志
docker-compose logs | grep -i error

# 查看Nginx访问日志
docker exec yixue-nginx tail -f /var/log/nginx/yixue_access.log
```

---

## 🔄 备份检查

### 数据备份
- [ ] 数据库备份脚本已配置
- [ ] 上传文件备份策略已制定
- [ ] Redis数据备份策略已制定

### 配置备份
- [ ] .env文件已备份
- [ ] Nginx配置已备份
- [ ] docker-compose.yml已备份

---

## 📞 故障排查

### 容器无法启动
```bash
# 查看详细日志
docker-compose logs [service-name]

# 检查配置
docker-compose config

# 重新构建
docker-compose build --no-cache
docker-compose up -d
```

### 数据库连接失败
```bash
# 检查容器
docker ps | grep mysql

# 检查密码
cat .env | grep DB_

# 进入容器测试
docker exec -it yixue-mysql mysql -u yixue -p
```

### API无法访问
```bash
# 检查端口
netstat -tulpn | grep 8080

# 检查Nginx
docker-compose logs yixue-nginx

# 检查PHP
docker-compose logs yixue-php
```

### H5页面空白
```bash
# 检查构建文件
ls -la h5/dist/

# 检查容器
docker-compose logs yixue-h5

# 检查API地址
cat ../1213yixuesuanming/1213yixuesuanming/config/website.js
```

---

## ✨ 部署完成确认

- [ ] 所有容器运行正常
- [ ] API可正常访问
- [ ] 数据库连接正常
- [ ] Redis连接正常
- [ ] H5前端可访问（如已部署）
- [ ] 功能测试通过
- [ ] 日志无严重错误
- [ ] 资源使用正常
- [ ] 备份策略已配置

---

## 📝 部署信息记录

```
部署时间: __________
服务器IP: __________
API地址: http://________:8080
H5地址: http://________:8081
数据库: yixue-mysql:3307
Redis: yixue-redis:6380

数据库密码: __________
Redis密码: __________

域名配置:
- API: __________
- H5: __________

备注:
__________
__________
```
