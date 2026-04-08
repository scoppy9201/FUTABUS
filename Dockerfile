# Sử dụng PHP 8.2 CLI làm base image
FROM php:8.2-cli

# Cài đặt các thư viện hệ thống cần thiết cho GD và các extension khác
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip

# Copy Composer từ image chính thức
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Thiết lập thư mục làm việc
WORKDIR /app

# Copy toàn bộ code vào container
COPY . .

# Cài đặt các dependencies của PHP
# Sử dụng --ignore-platform-reqs nếu bạn vẫn gặp lỗi về môi trường lúc build
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Phân quyền cho thư mục storage và bootstrap/cache (Rất quan trọng với Laravel)
RUN chmod -R 775 storage bootstrap/cache && \
    chown -R www-data:www-data /app

# Mở cổng (Railway sẽ tự động map biến $PORT này)
EXPOSE 8080

# Lệnh khởi chạy ứng dụng
# Railway sẽ truyền biến môi trường $PORT vào, mặc định nếu không có sẽ lấy 8080
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8080}