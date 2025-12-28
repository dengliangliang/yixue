#!/bin/bash
set -e

echo "🎨 开始构建H5前端..."

# 确定项目路径
if [ -d "../1213yixuesuanming/1213yixuesuanming" ]; then
    H5_PROJECT="../1213yixuesuanming/1213yixuesuanming"
    OUTPUT_DIR="./h5"
else
    echo "❌ 找不到H5项目目录"
    exit 1
fi

echo "📁 项目目录: $H5_PROJECT"
echo "📁 输出目录: $OUTPUT_DIR"

# 使用Docker容器构建（避免Node.js环境问题）
echo "🐳 使用Docker容器构建..."
docker run --rm \
    -v "$(cd $H5_PROJECT && pwd)":/app \
    -w /app \
    node:18-alpine \
    sh -c "npm install && npm run build:h5"

# 检查构建结果
if [ ! -d "$H5_PROJECT/dist/build/h5" ]; then
    echo "❌ 构建失败，未找到输出目录"
    exit 1
fi

# 清空并复制构建文件
echo "📁 复制构建文件到 $OUTPUT_DIR ..."
rm -rf "$OUTPUT_DIR"/*
mkdir -p "$OUTPUT_DIR"
cp -r "$H5_PROJECT/dist/build/h5"/* "$OUTPUT_DIR"/

# 验证文件
if [ -f "$OUTPUT_DIR/index.html" ]; then
    echo "✅ H5构建完成！"
    echo "📊 文件统计:"
    du -sh "$OUTPUT_DIR"
    ls -lh "$OUTPUT_DIR" | head -10
else
    echo "❌ 构建文件复制失败"
    exit 1
fi
