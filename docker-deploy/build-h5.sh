#!/bin/bash
set -e

echo "🎨 开始构建H5前端..."

# 进入H5项目目录
cd "$(dirname "$0")/../1213yixuesuanming/1213yixuesuanming" || exit 1

# 检查Node.js
if ! command -v node &> /dev/null; then
    echo "❌ Node.js未安装"
    exit 1
fi

# 安装依赖
if [ ! -d "node_modules" ]; then
    echo "📦 安装依赖..."
    npm install
fi

# 构建H5
echo "🔨 构建H5项目..."
npm run build:h5

# 检查构建结果
if [ ! -d "unpackage/dist/build/h5" ]; then
    echo "❌ 构建失败，未找到输出目录"
    exit 1
fi

# 复制到Docker目录
echo "📁 复制构建文件..."
cd ../../docker-deploy
rm -rf h5/dist
mkdir -p h5/dist
cp -r ../1213yixuesuanming/1213yixuesuanming/unpackage/dist/build/h5/* h5/dist/

echo "✅ H5构建完成！"
echo ""
echo "构建文件位置: docker-deploy/h5/dist/"
echo ""
echo "下一步:"
echo "  1. 检查 h5/dist/ 目录"
echo "  2. 运行 docker-compose up -d yixue-h5"
echo "  3. 访问 http://服务器IP:8081"
