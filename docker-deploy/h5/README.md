# H5前端部署说明

## 📦 构建方式

### 方式1: 本地构建后上传（推荐）

#### 在本地Windows环境
```bash
cd 1213yixuesuanming/1213yixuesuanming
npm install
npm run build:h5
```

#### 上传构建文件
```bash
# 将 unpackage/dist/build/h5/ 目录内容
# 上传到服务器 docker-deploy/h5/dist/
```

---

### 方式2: 服务器端构建

#### 在服务器上
```bash
cd /home/wwwroot/yixue/docker-deploy
bash build-h5.sh
```

**注意**: 服务器需要安装Node.js环境

---

## 🔧 配置API地址

### 修改前端配置
```javascript
// 1213yixuesuanming/1213yixuesuanming/config/website.js
export default {
    URL: 'http://服务器IP:8080',
    // 或
    URL: '/api',  // 使用相对路径，通过Nginx代理
}
```

---

## 📁 目录结构

```
h5/
├── Dockerfile          # Docker镜像配置
├── nginx.conf          # Nginx配置
├── README.md           # 本文档
└── dist/               # 构建文件（需要创建）
    ├── index.html
    ├── static/
    └── ...
```

---

##  部署命令

```bash
# 1. 构建H5（本地或服务器）
bash build-h5.sh

# 2. 启动H5容器
docker-compose up -d yixue-h5

# 3. 查看日志
docker-compose logs -f yixue-h5

# 4. 访问测试
curl http://localhost:8081
```

---

## ⚠️ 注意事项

1. **dist目录**: 必须包含构建后的文件
2. **API地址**: 构建前确认配置正确
3. **端口**: 默认使用8081端口
4. **代理**: API请求通过Nginx代理到后端
