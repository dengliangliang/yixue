FROM nginx:1.24-alpine

# 复制构建后的H5文件
COPY dist/ /usr/share/nginx/html/

# 复制Nginx配置
COPY nginx.conf /etc/nginx/conf.d/default.conf

# 创建日志目录
RUN mkdir -p /var/log/nginx

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
