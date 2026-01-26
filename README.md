# HangTruyen - Website Đọc Truyện Tranh Online

Hangtruyen - Website đọc truyện tranh online miễn phí được xây dựng bằng Laravel.

## Giới thiệu

Website sử dụng API từ [OTruyen API](https://docs.otruyenapi.com/)

## Yêu cầu hệ thống

- PHP >= 8.0
- Composer
- MySQL >= 5.7 hoặc MariaDB >= 10.2
- Node.js và NPM (cho frontend assets)
- Web server (Apache/Nginx) hoặc PHP built-in server

## Cài đặt

### 1. Clone repository

```bash
git clone https://github.com/tophvn/hangtruyen_source.git
cd hangtruyen_source
```

### 2. Cài đặt dependencies

```bash
composer install
npm install
```

### 3. Cấu hình môi trường

Sao chép file `.env.example` thành `.env`:

```bash
cp .env.example .env
```

Tạo APP_KEY:

```bash
php artisan key:generate
```

### 4. Cấu hình file .env

Mở file `.env` và cấu hình các thông tin sau:

#### Cấu hình ứng dụng cơ bản

```env
APP_NAME=HangTruyen
APP_ENV=local          # local, staging, production
APP_KEY=               # Tự động tạo sau khi chạy php artisan key:generate
APP_DEBUG=true         # false trên production
APP_URL=http://localhost
```

#### Cấu hình Database

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1      # Địa chỉ MySQL server
DB_PORT=3306           # Port MySQL (mặc định 3306)
DB_DATABASE=hangtruyen # Tên database
DB_USERNAME=root       # Username MySQL
DB_PASSWORD=           # Password MySQL
```

**Lưu ý:** Đảm bảo đã tạo database trước khi chạy migrations.

#### Cấu hình Logging

```env
LOG_CHANNEL=stack      # stack, single, daily, syslog, errorlog
LOG_LEVEL=debug        # debug, info, notice, warning, error, critical, alert, emergency
```

#### Cấu hình Cache và Session

```env
CACHE_DRIVER=file      # file, redis, memcached
SESSION_DRIVER=file    # file, redis, database
SESSION_LIFETIME=120   # Thời gian session (phút)
```

#### Cấu hình Google OAuth

```env
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

**Hướng dẫn lấy Google OAuth credentials:**

1. Truy cập [Google Cloud Console](https://console.cloud.google.com/)
2. Tạo project mới
3. Bật Google+ API
4. Tạo OAuth 2.0 Client ID
5. Thêm Authorized redirect URIs: `http://localhost:8000/auth/google/callback` (cho local) và URL production của bạn
6. Copy Client ID và Client Secret vào file `.env`

### Chạy ứng dụng

**Development:**

```bash
php artisan serve
```

Truy cập: `http://localhost:8000`

**Production:**

Cấu hình web server (Apache/Nginx) trỏ đến thư mục `public/`


## License

MIT or project-specific; see repository terms.
