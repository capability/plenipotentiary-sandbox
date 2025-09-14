FROM nginx:alpine

# Copy Laravel public dir
COPY ./apps/backend/public /var/www/html/public

# Copy prod nginx config
COPY ./apps/backend/docker/nginx/default.prod.conf /etc/nginx/conf.d/default.conf
