#!/bin/bash
# 端口检查和自动分配脚本

set -e

echo "🔍 检查端口占用情况..."

# 定义端口配置
declare -A PORTS=(
    ["API_PORT"]="8080 8082 8083"
    ["H5_PORT"]="8081 8084 8085"
    ["MYSQL_PORT"]="3307 3308 3309"
    ["REDIS_PORT"]="6380 6381 6382"
)

# 检查端口是否被占用
check_port() {
    local port=$1
    if lsof -Pi :$port -sTCP:LISTEN -t >/dev/null 2>&1 || netstat -tuln 2>/dev/null | grep -q ":$port "; then
        return 1  # 端口被占用
    else
        return 0  # 端口可用
    fi
}

# 检查防火墙端口
check_firewall() {
    local port=$1
    if command -v firewall-cmd &> /dev/null; then
        if ! firewall-cmd --zone=public --list-ports 2>/dev/null | grep -q "${port}/tcp"; then
            echo "⚠️  端口 $port 未在防火墙中开放"
            return 1
        fi
    fi
    return 0
}

# 开放防火墙端口
open_firewall_port() {
    local port=$1
    if command -v firewall-cmd &> /dev/null; then
        echo "🔓 开放防火墙端口 $port ..."
        firewall-cmd --zone=public --add-port=${port}/tcp --permanent >/dev/null 2>&1
        firewall-cmd --zone=docker --add-port=${port}/tcp --permanent >/dev/null 2>&1 || true
        firewall-cmd --reload >/dev/null 2>&1
        echo "✅ 端口 $port 已开放"
    fi
}

# 为服务选择可用端口
select_port() {
    local service=$1
    local ports=${PORTS[$service]}
    
    for port in $ports; do
        if check_port $port; then
            echo "$port"
            
            # 检查并开放防火墙
            if ! check_firewall $port; then
                open_firewall_port $port
            fi
            
            return 0
        fi
    done
    
    echo "❌ 所有备用端口都被占用: $ports" >&2
    return 1
}

# 生成 .env 文件
generate_env() {
    local api_port=$(select_port "API_PORT")
    local h5_port=$(select_port "H5_PORT")
    local mysql_port=$(select_port "MYSQL_PORT")
    local redis_port=$(select_port "REDIS_PORT")
    
    if [ -z "$api_port" ] || [ -z "$h5_port" ] || [ -z "$mysql_port" ] || [ -z "$redis_port" ]; then
        echo "❌ 无法分配所有必需的端口"
        exit 1
    fi
    
    echo ""
    echo "📋 端口分配结果:"
    echo "  API端口:   $api_port"
    echo "  H5端口:    $h5_port"
    echo "  MySQL端口: $mysql_port"
    echo "  Redis端口: $redis_port"
    echo ""
    
    # 更新 docker-compose.yml 中的端口
    if [ -f docker-compose.yml ]; then
        echo "📝 更新 docker-compose.yml 端口配置..."
        sed -i.bak "s/- \"[0-9]*:80\"  *# HTTP端口/- \"$api_port:80\"      # HTTP端口/" docker-compose.yml
        sed -i.bak "s/- \"[0-9]*:80\"  *# H5访问端口/- \"$h5_port:80\"      # H5访问端口/" docker-compose.yml
        sed -i.bak "s/- \"[0-9]*:3306\"/- \"$mysql_port:3306\"/" docker-compose.yml
        sed -i.bak "s/- \"[0-9]*:6379\"/- \"$redis_port:6379\"/" docker-compose.yml
        echo "✅ 端口配置已更新"
    fi
    
    return 0
}

# 主函数
main() {
    echo "🚀 开始端口检查..."
    echo ""
    
    generate_env
    
    echo ""
    echo "✅ 端口检查完成！"
    echo ""
    echo "📌 访问地址:"
    echo "  - API:   http://服务器IP:$(select_port API_PORT)"
    echo "  - H5:    http://服务器IP:$(select_port H5_PORT)"
    echo "  - MySQL: 服务器IP:$(select_port MYSQL_PORT)"
    echo "  - Redis: 服务器IP:$(select_port REDIS_PORT)"
}

main
