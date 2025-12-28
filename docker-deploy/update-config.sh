#!/bin/bash
set -e

echo "🔧 更新应用配置以适配Docker环境..."

# 进入项目目录
cd "$(dirname "$0")/.." || exit 1

# 备份原配置
echo "📦 备份原配置文件..."
cp 1213-easy-to-learn/application/database.php 1213-easy-to-learn/application/database.php.bak
cp 1213-easy-to-learn/application/extra/queue.php 1213-easy-to-learn/application/extra/queue.php.bak

# 更新数据库配置
echo "🗄️  更新数据库配置..."
cat > 1213-easy-to-learn/application/database.php << 'EOF'
<?php
use think\Env;

return [
    'type'            => Env::get('database.type', 'mysql'),
    'hostname'        => getenv('DB_HOST') ?: 'yixue-mysql',
    'database'        => getenv('DB_NAME') ?: 'yixue',
    'username'        => getenv('DB_USER') ?: 'yixue',
    'password'        => getenv('DB_PASS') ?: 'yixue2024',
    'hostport'        => Env::get('database.hostport', ''),
    'dsn'             => '',
    'params'          => [],
    'charset'         => Env::get('database.charset', 'utf8mb4'),
    'prefix'          => Env::get('database.prefix', 'fa_'),
    'debug'           => Env::get('database.debug', false),
    'deploy'          => 0,
    'rw_separate'     => false,
    'master_num'      => 1,
    'slave_no'        => '',
    'fields_strict'   => true,
    'resultset_type'  => 'array',
    'auto_timestamp'  => false,
    'datetime_format' => false,
    'sql_explain'     => false,
];
EOF

# 更新Redis配置
echo "📊 更新Redis配置..."
cat > 1213-easy-to-learn/application/extra/queue.php << 'EOF'
<?php
return [
    'connector'  => 'Redis',
    'expire'     => 0,
    'default'    => 'default',
    'host'       => getenv('REDIS_HOST') ?: 'yixue-redis',
    'port'       => 6379,
    'password'   => getenv('REDIS_PASSWORD') ?: '',
    'select'     => 0,
    'timeout'    => 0,
    'persistent' => false,
];
EOF

echo "✅ 配置更新完成！"
echo ""
echo "已更新的文件:"
echo "  - application/database.php"
echo "  - application/extra/queue.php"
echo ""
echo "备份文件:"
echo "  - application/database.php.bak"
echo "  - application/extra/queue.php.bak"
echo ""
echo "下一步:"
echo "  1. 检查配置是否正确"
echo "  2. 运行 cd docker-deploy && bash deploy.sh"
