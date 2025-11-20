# H5前端部署指南

## 📱 部署方式

### 方式1: Docker容器部署（推荐）

#### 步骤1: 本地构建H5
```bash
cd docker-deploy
bash build-h5.sh
```

#### 步骤2: 部署到服务器
```bash
# 上传整个项目到服务器
scp -r ../1213易学 root@服务器:/home/wwwroot/yixue

# SSH登录服务器
ssh root@服务器

# 进入目录
cd /home/wwwroot/yixue/docker-deploy

# 启动H5服务
docker-compose up -d yixue-h5
```

#### 步骤3: 访问测试
```
http://服务器IP:8081
```

---

### 方式2: 宿主机Nginx托管

#### 步骤1: 本地构建
```bash
cd 1213yixuesuanming/1213yixuesuanming
npm install
npm run build:h5
```

#### 步骤2: 上传到服务器
```bash
scp -r unpackage/dist/build/h5/* root@服务器:/home/wwwroot/yixue-h5/
```

#### 步骤3: 配置Nginx
```nginx
# /usr/local/nginx/conf/vhost/yixue-h5.conf
server {
    listen 80;
    server_name h5.yixue.linqingkeji.com;
    root /home/wwwroot/yixue-h5;
    index index.html;

    location / {
        try_files $uri $uri/ /index.html;
    }

    # API代理
    location /api/ {
        proxy_pass http://127.0.0.1:8080/;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

#### 步骤4: 重载Nginx
```bash
nginx -t
nginx -s reload
```

---

## 🔧 配置API地址

### 修改前端配置
```javascript
// 1213yixuesuanming/1213yixuesuanming/config/website.js
export default {
    URL: 'http://服务器IP:8080',  // 或使用域名
    // 或者
    URL: 'https://api.yixue.linqingkeji.com',
}
```

### 重新构建
```bash
npm run build:h5
```

---

## 📊 端口说明

```
8080  → 后端API HTTP
8443  → 后端API HTTPS
8081  → H5前端
3307  → MySQL
6380  → Redis
```

---

## 🌐 域名配置

### H5使用独立域名
```nginx
# 宿主机Nginx配置
server {
    listen 80;
    server_name h5.yixue.linqingkeji.com;
    
    location / {
        proxy_pass http://127.0.0.1:8081;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

### API使用独立域名
```nginx
server {
    listen 80;
    server_name api.yixue.linqingkeji.com;
    
    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

---

## ✅ 验证部署

### 检查容器状态
```bash
docker ps | grep yixue
```

### 测试H5访问
```bash
curl http://localhost:8081
```

### 测试API调用
```bash
# 在H5页面打开浏览器控制台
# 检查Network标签中的API请求
```

---

## 🔧 常见问题

### Q: H5页面空白
```bash
# 检查构建文件
ls -la h5/dist/

# 查看容器日志
docker-compose logs yixue-h5

# 检查Nginx配置
docker exec yixue-h5 cat /etc/nginx/conf.d/default.conf
```

### Q: API调用失败
```bash
# 检查API地址配置
cat 1213yixuesuanming/1213yixuesuanming/config/website.js

# 检查网络连通性
docker exec yixue-h5 ping yixue-nginx

# 查看API日志
docker-compose logs yixue-nginx
```

### Q: 跨域问题
```nginx
# 在后端Nginx添加CORS头
add_header Access-Control-Allow-Origin *;
add_header Access-Control-Allow-Methods 'GET, POST, OPTIONS';
add_header Access-Control-Allow-Headers 'DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Authorization';
```

---

## 📝 注意事项

1. **构建环境**: 需要Node.js环境
2. **API地址**: 构建前确认API地址正确
3. **端口开放**: 确保8081端口开放
4. **跨域配置**: 如需跨域需配置CORS
5. **缓存清理**: 更新后清理浏览器缓存
