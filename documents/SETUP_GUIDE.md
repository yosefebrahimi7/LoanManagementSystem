# راهنمای راه‌اندازی سیستم مدیریت وام

## پیش‌نیازها
- PHP 8.2+، Composer، Node.js 18+، MySQL

## راه‌اندازی Backend
```bash
cd web-api
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve --host=0.0.0.0 --port=8000
```

## اجرای Queue Worker (برای ارسال ایمیل)
```bash
cd web-api
php artisan queue:work
```

## راه‌اندازی Frontend
```bash
cd client
npm install
npm run dev
```

## Seed کردن دیتاها
```bash
cd web-api
php artisan db:seed
```

## تست سیستم
1. Backend: `http://localhost:8000`
2. Frontend: `http://localhost:5174`
3. ثبت نام یا ورود با ایمیل: `admin@example.com` و رمز: `password`

## نکات مهم
- تنظیم پایگاه داده در فایل `.env`
- تنظیم SMTP در فایل `.env`:
  ```
  MAIL_MAILER=smtp
  MAIL_HOST=smtp.gmail.com
  MAIL_PORT=587
  MAIL_USERNAME=yourmain@gmail.com
  MAIL_PASSWORD=token
  MAIL_ENCRYPTION=tls
  ```
- **برای ارسال ایمیل**: باید `php artisan queue:work` را اجرا کنید
- Backend: `http://localhost:8000`
- Frontend: `http://localhost:5174`
- API Endpoints: `/api/auth/login`, `/api/auth/register`
