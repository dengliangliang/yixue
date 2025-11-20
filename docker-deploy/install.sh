#!/bin/bash
set -e

echo "🚀 1213易学项目 - 一键部署脚本"
echo "================================"
echo ""

# 配置
REPO_URL="https://github.com/dengliangliang/yixue.git"
INSTALL_DIR="/home/wwwroot/yixue"

# 检查是否为root用户
if [ "$EUID" -ne 0 ]; then 
    echo "❌ 请使用root用户运行此脚本"
    exit 1
fi

# 检查Git
echo "📦 检查Git..."
if ! command -v git &> /dev/null; then
    echo "Git未安装，正在安装..."
    if command -v yum &> /dev/null; then
        yum install -y git
    elif command -v apt-get &> /dev/null; then
        apt-get update && apt-get install -y git
    else
        echo "❌ 无法自动安装Git，请手动安装"
        exit 1
    fi
fi

# 检查Docker
echo "🐳 检查Docker..."
if ! command -v docker &> /dev/null; then
    echo "Docker未安装，正在安装..."
    curl -fsSL https://get.docker.com | bash
    systemctl start docker
    systemctl enable docker
fi

# 检查Docker Compose
echo "📦 检查Docker Compose..."
if ! command -v docker-compose &> /dev/null; then
    echo "Docker Compose未安装，正在安装..."
    curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
    chmod +x /usr/local/bin/docker-compose
fi

# 克隆项目
echo "📥 克隆项目..."
if [ -d "$INSTALL_DIR" ]; then
    echo "⚠️  目录已存在: $INSTALL_DIR"
    read -p "是否删除并重新克隆？(y/n): " confirm
    if [ "$confirm" = "y" ] || [ "$confirm" = "Y" ]; then
        rm -rf "$INSTALL_DIR"
    else
        echo "使用现有目录"
        cd "$INSTALL_DIR"
    fi
fi

if [ ! -d "$INSTALL_DIR" ]; then
    git clone "$REPO_URL" "$INSTALL_DIR"
    cd "$INSTALL_DIR"
fi

# 进入部署目录
cd "$INSTALL_DIR/docker-deploy"

# 检查.env文件
if [ ! -f .env ]; then
    echo "📝 配置环境变量..."
    cp .env.example .env
    
    echo ""
    echo "⚠️  请配置以下信息:"
    echo ""
    
    # 数据库密码
    read -p "数据库密码 [默认: JCpYZwjxmCDdHcCF]: " db_pass
    db_pass=${db_pass:-JCpYZwjxmCDdHcCF}
    sed -i "s/DB_PASS=.*/DB_PASS=$db_pass/" .env
    sed -i "s/DB_ROOT_PASS=.*/DB_ROOT_PASS=${db_pass}_root/" .env
    
    # Redis密码（可选）
    read -p "Redis密码 [默认: 无密码，直接回车]: " redis_pass
    if [ -n "$redis_pass" ]; then
        sed -i "s/REDIS_PASSWORD=.*/REDIS_PASSWORD=$redis_pass/" .env
        # 启用Redis密码
        sed -i "s/# requirepass/requirepass/" redis/redis.conf
        sed -i "s/requirepass .*/requirepass $redis_pass/" redis/redis.conf
    fi
    
    echo "✅ 环境变量配置完成"
fi

# 更新应用配置
echo "🔧 更新应用配置..."
bash update-config.sh

# 创建数据目录
echo "📁 创建数据目录..."
mkdir -p data/mysql data/redis data/logs/nginx data/ssl

# 询问是否部署H5
read -p "是否部署H5前端？(y/n) [默认: y]: " deploy_h5
deploy_h5=${deploy_h5:-y}

if [ "$deploy_h5" = "y" ] || [ "$deploy_h5" = "Y" ]; then
    echo "🎨 准备H5部署..."
    echo "⚠️  H5需要在本地构建后上传，或在服务器安装Node.js后构建"
    echo ""
    
    if command -v node &> /dev/null; then
        echo "检测到Node.js，尝试构建H5..."
        bash build-h5.sh || {
            echo "⚠️  H5构建失败，将跳过H5部署"
            deploy_h5="n"
        }
    else
        echo "⚠️  未检测到Node.js，跳过H5构建"
        echo "提示: 可以在本地构建后上传到 docker-deploy/h5/dist/"
        deploy_h5="n"
    fi
fi

# 停止旧容器
echo "🛑 停止旧容器..."
docker-compose down || true

# 构建镜像
echo "🔨 构建Docker镜像..."
docker-compose build --no-cache

# 启动容器
echo "▶️  启动容器..."
if [ "$deploy_h5" = "y" ] || [ "$deploy_h5" = "Y" ]; then
    docker-compose up -d
else
    docker-compose up -d yixue-nginx yixue-php yixue-mysql yixue-redis
fi

# 等待服务就绪
echo "⏳ 等待服务启动..."
sleep 20

# 检查服务状态
echo "✅ 检查服务状态..."
docker-compose ps

# 获取服务器IP
SERVER_IP=$(curl -s ifconfig.me || hostname -I | awk '{print $1}')

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✨ 部署完成！"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📍 访问地址:"
echo "  - API HTTP:  http://$SERVER_IP:8080"
echo "  - API HTTPS: https://$SERVER_IP:8443"
if [ "$deploy_h5" = "y" ] || [ "$deploy_h5" = "Y" ]; then
    echo "  - H5前端:    http://$SERVER_IP:8081"
fi
echo ""
echo "🗄️  数据库连接:"
echo "  - Host: $SERVER_IP:3307"
echo "  - Database: yixue"
echo "  - User: yixue"
echo "  - Password: $db_pass"
echo ""
echo "📊 Redis连接:"
echo "  - Host: $SERVER_IP:6380"
if [ -n "$redis_pass" ]; then
    echo "  - Password: $redis_pass"
else
    echo "  - Password: (无密码)"
fi
echo ""
echo "🔧 常用命令:"
echo "  - 查看日志: cd $INSTALL_DIR/docker-deploy && docker-compose logs -f"
echo "  - 重启服务: cd $INSTALL_DIR/docker-deploy && docker-compose restart"
echo "  - 停止服务: cd $INSTALL_DIR/docker-deploy && docker-compose down"
echo "  - 进入容器: docker exec -it yixue-php bash"
echo ""
echo "📝 配置文件位置:"
echo "  - 环境变量: $INSTALL_DIR/docker-deploy/.env"
echo "  - 应用配置: $INSTALL_DIR/1213-easy-to-learn/application/"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
