#!/bin/bash
set -e

echo "🎨 开始构建H5前端..."

# 检查是否在Docker容器中
if [ -f /.dockerenv ]; then
    echo "🐳 在Docker容器中构建..."
    H5_DIR="/workspace/1213yixuesuanming/1213yixuesuanming"
    OUTPUT_DIR="/workspace/docker-deploy/h5"
else
    echo "💻 在宿主机中构建..."
    H5_DIR="../1213yixuesuanming/1213yixuesuanming"
    OUTPUT_DIR="./h5"
fi

cd "$H5_DIR" || exit 1

# 检查Node.js
if ! command -v node &> /dev/null; then
    echo "❌ Node.js未安装，尝试安装..."
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
    apt-get install -y nodejs
fi

echo "📦 安装依赖..."
npm install

echo "🔨 构建H5..."
npm run build:h5

echo "📁 复制构建文件..."
rm -rf "$OUTPUT_DIR"/*
mkdir -p "$OUTPUT_DIR"
cp -r dist/build/h5/* "$OUTPUT_DIR"/

echo "✅ H5构建完成！输出目录: $OUTPUT_DIR"
