#!/bin/bash
set -e

echo "🚀 开始部署1213易学项目（后端API + H5前端）..."

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
    echo "请编辑.env文件配置密码"
    exit 1
fi

# 创建必要的目录
echo "📁 创建数据目录..."
mkdir -p data/mysql data/redis data/logs/nginx data/ssl

# 构建H5前端
echo "🎨 构建H5前端..."
if [ -f "build-h5.sh" ]; then
    bash build-h5.sh || {
        echo "⚠️  H5构建失败，将跳过H5部署"
        SKIP_H5=true
    }
else
    echo "⚠️  未找到build-h5.sh，将跳过H5部署"
    SKIP_H5=true
fi

# 停止旧容器
echo "🛑 停止旧容器..."
docker-compose down || true

# 构建镜像
echo "🔨 构建Docker镜像..."
docker-compose build --no-cache

# 启动容器
echo "▶️  启动容器..."
docker-compose up -d

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
if [ "$SKIP_H5" != "true" ]; then
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
echo "  - Password: (无密码)"
echo ""
echo "常用命令:"
echo "  - 查看日志: docker-compose logs -f [service-name]"
echo "  - 重启服务: docker-compose restart [service-name]"
echo "  - 停止服务: docker-compose down"
echo "  - 进入容器: docker exec -it yixue-php bash"
if [ "$SKIP_H5" != "true" ]; then
    echo "  - 重新构建H5: bash build-h5.sh && docker-compose restart yixue-h5"
fi
