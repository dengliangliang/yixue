# 1213易学项目

基于ThinkPHP + FastAdmin的易学测算平台，支持Docker一键部署。

## ✨ 特性

- 🎯 四柱八字测算
- 🌙 农历阳历转换
- 📱 uni-app H5前端
- 🐳 Docker容器化部署
- 🔒 独立数据库和Redis
- ⚡ 一键部署脚本

## 🚀 快速部署

### 方式1: 一键安装脚本（推荐）

```bash
# 下载并执行安装脚本
curl -fsSL https://raw.githubusercontent.com/dengliangliang/yixue/main/docker-deploy/install.sh | bash

# 或者
wget -O- https://raw.githubusercontent.com/dengliangliang/yixue/main/docker-deploy/install.sh | bash
```

### 方式2: 手动部署

```bash
# 1. 克隆项目
git clone https://github.com/dengliangliang/yixue.git
cd yixue/docker-deploy

# 2. 更新配置
bash update-config.sh

# 3. 一键部署
bash deploy.sh
```

## 📊 服务端口

```
8080  → API HTTP
8443  → API HTTPS
8081  → H5前端
3307  → MySQL
6380  → Redis
```

## 🌐 访问地址

```
API:  http://服务器IP:8080
H5:   http://服务器IP:8081
```

## 📁 项目结构

```
yixue/
├── 1213-easy-to-learn/          # 后端API（ThinkPHP）
├── 1213yixuesuanming/           # H5前端（uni-app）
├── docker-deploy/               # Docker部署配置
│   ├── install.sh               # 一键安装脚本
│   ├── deploy.sh                # 部署脚本
│   ├── docker-compose.yml       # Docker编排
│   ├── nginx/                   # Nginx配置
│   ├── php/                     # PHP配置
│   ├── mysql/                   # MySQL配置
│   ├── redis/                   # Redis配置
│   └── h5/                      # H5配置
└── yixue.sql                    # 数据库初始化文件
```

## 🔧 环境要求

- Docker 20.10+
- Docker Compose 1.29+
- 2GB+ 内存
- 2GB+ 磁盘空间

## 📖 文档

- [快速开始](docker-deploy/QUICKSTART.md)
- [详细文档](docker-deploy/README.md)
- [H5部署](docker-deploy/H5-DEPLOY.md)
- [检查清单](docker-deploy/CHECKLIST.md)

## 🛠️ 技术栈

### 后端
- PHP 7.4
- ThinkPHP 5.1
- FastAdmin
- MySQL 5.7
- Redis 7.2
- Nginx 1.24

### 前端
- uni-app
- Vue.js
- uView UI

## 📝 配置说明

### 数据库配置
```env
DB_HOST=yixue-mysql
DB_NAME=yixue
DB_USER=yixue
DB_PASS=your_password
```

### Redis配置
```env
REDIS_HOST=yixue-redis
REDIS_PORT=6379
REDIS_PASSWORD=
```

## 🔄 更新部署

```bash
cd /home/wwwroot/yixue
git pull
cd docker-deploy
docker-compose restart
```

## 🐛 故障排查

### 查看日志
```bash
cd /home/wwwroot/yixue/docker-deploy
docker-compose logs -f
```

### 重启服务
```bash
docker-compose restart
```

### 重新部署
```bash
docker-compose down
bash deploy.sh
```

## 📞 技术支持

如遇问题，请查看:
1. [详细文档](docker-deploy/README.md)
2. [检查清单](docker-deploy/CHECKLIST.md)
3. [GitHub Issues](https://github.com/dengliangliang/yixue/issues)

## 📄 License

MIT License

## 🙏 致谢

- [ThinkPHP](https://www.thinkphp.cn/)
- [FastAdmin](https://www.fastadmin.net/)
- [uni-app](https://uniapp.dcloud.io/)
- [lunar-php](https://github.com/6tail/lunar-php)
