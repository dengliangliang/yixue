#!/bin/bash
set -e

echo "🚀 开始完整部署（后端API + H5前端）..."

# 检查Docker
if ! command -v docker &> /dev/null; then
    echo "❌ Docker未安装"
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose未安装"
    exit 1
fi

# 检查.env文件
if [ ! -f .env ]; then
    echo "⚠️  .env文件不存在，从.env.example复制..."
    cp .env.example .env
    echo "❌ 请编辑.env文件配置密码后重新运行"
    exit 1
fi

# 创建必要的目录
echo "📁 创建数据目录..."
mkdir -p data/mysql data/redis data/logs/nginx data/ssl

# 询问是否部署H5
read -p "是否同时部署H5前端？(y/n): " deploy_h5

if [ "$deploy_h5" = "y" ] || [ "$deploy_h5" = "Y" ]; then
    echo "🎨 构建H5前端..."
    bash build-h5.sh || {
        echo "⚠️  H5构建失败，仅部署后端API"
        deploy_h5="n"
    }
fi

# 停止旧容器
echo "🛑 停止旧容器..."
docker-compose down || true

# 构建镜像
echo "🔨 构建Docker镜像..."
if [ "$deploy_h5" = "y" ] || [ "$deploy_h5" = "Y" ]; then
    docker-compose build --no-cache
else
    docker-compose build --no-cache yixue-nginx yixue-php yixue-mysql yixue-redis
fi

# 启动容器
echo "▶️  启动容器..."
if [ "$deploy_h5" = "y" ] || [ "$deploy_h5" = "Y" ]; then
    docker-compose up -d
else
    docker-compose up -d yixue-nginx yixue-php yixue-mysql yixue-redis
fi

# 等待服务就绪
echo "⏳ 等待服务启动..."
sleep 15

# 检查服务状态
echo "✅ 检查服务状态..."
docker-compose ps

# 查看日志
echo ""
echo "📋 最近日志:"
docker-compose logs --tail=30

echo ""
echo "✨ 部署完成！"
echo ""
echo "访问地址:"
echo "  - API HTTP:  http://服务器IP:8080"
echo "  - API HTTPS: https://服务器IP:8443"

if [ "$deploy_h5" = "y" ] || [ "$deploy_h5" = "Y" ]; then
    echo "  - H5前端:    http://服务器IP:8081"
fi

echo ""
echo "数据库连接:"
echo "  - Host: 服务器IP:3307"
echo "  - Database: yixue"
echo "  - User: yixue"
echo ""
echo "Redis连接:"
echo "  - Host: 服务器IP:6380"
echo ""
echo "常用命令:"
echo "  - 查看日志: docker-compose logs -f [service-name]"
echo "  - 重启服务: docker-compose restart [service-name]"
echo "  - 停止服务: docker-compose down"
echo "  - 进入容器: docker exec -it yixue-php bash"
