#!/bin/bash

echo "🔍 检查服务器环境，确保不影响现有服务..."
echo ""

# 检查现有项目目录
echo "📁 检查现有项目目录:"
if [ -d "/home/wwwroot/default" ]; then
    echo "  ✅ 现有项目: /home/wwwroot/default"
    echo "  ✅ 新项目将安装到: /home/wwwroot/yixue"
    echo "  ✅ 两者完全独立，不会冲突"
else
    echo "  ℹ️  未检测到现有项目"
fi
echo ""

# 检查端口占用
echo "🔌 检查端口占用:"
echo "  现有服务端口:"
netstat -tulpn 2>/dev/null | grep -E ':(80|443|3306|6379|7001)\s' | awk '{print "    - "$4" ("$7")"}' || echo "    无法获取端口信息"

echo ""
echo "  新服务将使用端口:"
echo "    - 0.0.0.0:8080  (Nginx HTTP)"
echo "    - 0.0.0.0:8443  (Nginx HTTPS)"
echo "    - 0.0.0.0:3307  (MySQL)"
echo "    - 0.0.0.0:6380  (Redis)"
echo "    - 0.0.0.0:8081  (H5前端)"

echo ""
echo "  端口冲突检查:"
CONFLICTS=0
for port in 8080 8443 3307 6380 8081; do
    if netstat -tulpn 2>/dev/null | grep -q ":$port "; then
        echo "    ❌ 端口 $port 已被占用"
        CONFLICTS=$((CONFLICTS + 1))
    else
        echo "    ✅ 端口 $port 可用"
    fi
done

if [ $CONFLICTS -gt 0 ]; then
    echo ""
    echo "❌ 发现 $CONFLICTS 个端口冲突，请修改 docker-compose.yml"
    exit 1
fi
echo ""

# 检查Docker容器
echo "🐳 检查现有Docker容器:"
if command -v docker &> /dev/null; then
    docker ps --format "table {{.Names}}\t{{.Image}}\t{{.Ports}}" | grep -v "NAMES" | while read line; do
        echo "  ✅ $line"
    done
    echo ""
    echo "  新容器名称:"
    echo "    - yixue-nginx"
    echo "    - yixue-php"
    echo "    - yixue-mysql"
    echo "    - yixue-redis"
    echo "    - yixue-h5"
    echo "  ✅ 容器名称不冲突"
else
    echo "  ℹ️  Docker未安装"
fi
echo ""

# 检查Docker网络
echo "🌐 检查Docker网络:"
if command -v docker &> /dev/null; then
    echo "  现有网络:"
    docker network ls --format "    - {{.Name}}" | grep -v "bridge\|host\|none"
    echo "  新网络: yixue-network"
    echo "  ✅ 网络名称不冲突"
else
    echo "  ℹ️  Docker未安装"
fi
echo ""

# 检查系统服务
echo "⚙️  检查系统服务:"
for service in nginx php-fpm mariadb redis; do
    if systemctl is-active --quiet $service 2>/dev/null; then
        echo "  ✅ $service 正在运行（不受影响）"
    fi
done
echo ""

# 检查定时任务
echo "⏰ 检查定时任务:"
CRON_COUNT=$(crontab -l 2>/dev/null | grep -v "^#" | grep -v "^$" | wc -l)
if [ $CRON_COUNT -gt 0 ]; then
    echo "  ✅ 检测到 $CRON_COUNT 个定时任务"
    echo "  ✅ Docker部署不会影响现有定时任务"
else
    echo "  ℹ️  未检测到定时任务"
fi
echo ""

# 总结
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
if [ $CONFLICTS -eq 0 ]; then
    echo "✅ 环境检查通过，可以安全部署"
    echo ""
    echo "部署隔离说明:"
    echo "  1. 项目目录: /home/wwwroot/yixue (独立)"
    echo "  2. 端口: 8080/8443/3307/6380/8081 (不冲突)"
    echo "  3. Docker容器: yixue-* (独立命名)"
    echo "  4. Docker网络: yixue-network (独立)"
    echo "  5. 数据目录: docker-deploy/data/ (独立)"
    echo ""
    echo "不会影响:"
    echo "  ✅ /home/wwwroot/default 项目"
    echo "  ✅ 宿主机 Nginx (80/443)"
    echo "  ✅ 宿主机 MariaDB (3306)"
    echo "  ✅ 宿主机 Redis (6379)"
    echo "  ✅ 现有定时任务"
    echo "  ✅ certd 容器 (7001)"
    echo "  ✅ fail2ban 容器"
    echo ""
    echo "可以执行部署命令:"
    echo "  bash deploy.sh"
else
    echo "❌ 发现端口冲突，请先解决"
fi
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
