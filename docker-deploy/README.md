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
