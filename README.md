# Loan Management System 🏦

سیستم مدیریت وام با قابلیت‌های کامل کیف پول، پرداخت از طریق درگاه زرین‌پال و UI/UX مدرن.

## 🚀 ویژگی‌های کلیدی

- ✅ **احراز هویت و مدیریت نقش‌ها** (ادمین/کاربر)
- ✅ **سیستم وام** با محاسبه خودکار اقساط و بهره
- ✅ **کیف پول مشترک برای ادمین‌ها** و کیف پول شخصی برای کاربران
- ✅ **شارژ کیف پول** از طریق درگاه زرین‌پال
- ✅ **پرداخت اقساط** از طریق درگاه زرین‌پال
- ✅ **محاسبه جریمه** برای قسط‌های معوقه
- ✅ **اعلان‌های لحظه‌ای** برای رویدادهای مهم
- ✅ **UI/UX مدرن** با React + TypeScript + DaisyUI
- ✅ **API Documentation** کامل با Swagger

## 📦 نصب و راه‌اندازی

### تکنولوژی‌ها
- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: React 19 + TypeScript 5.9
- **Database**: MySQL 8.0+ / MariaDB
- **Package Manager**: Composer 2.x + npm 18+

### پیش‌نیازها
- PHP 8.2+ با extensions: pdo, pdo_mysql, mbstring, openssl
- Composer 2.x
- MySQL/MariaDB 8.0+ یا PostgreSQL
- Node.js 18+ و npm/yarn

### Backend (Laravel)

```bash
cd web-api

# نصب وابستگی‌ها
composer install

# تنظیم محیط
cp ../documents/env.template .env
php artisan key:generate

# تنظیم دیتابیس
# فایل .env را ویرایش کنید

# اجرای migration و seeder
php artisan migrate:fresh --seed

# Generate Swagger
php artisan l5-swagger:generate

# اجرا
php artisan serve
```

### Frontend (React)

```bash
cd client

# نصب وابستگی‌ها
npm install

# اجرا
npm run dev
```

## 🔑 حساب‌های پیش‌فرض

- **Admin**: `admin@example.com` / `password`
- **User**: `test1@example.com` / `password`

## 📚 مستندات

- [API Documentation](./documents/api-readme.md)
- [Wallet Implementation](./documents/wallet-implementation.md)
- [Feature Summary](./documents/feature-summary.md)
- [Database Design](./documents/database-design.md)
- [Swagger UI](http://localhost:8000/api/documentation)

## 🧪 تست‌ها

```bash
cd web-api
php artisan test
```

**49 تست جامع** با **199 Assertion** شامل:
- ✅ **Auth Tests**: 9 تست
- ✅ **Loan Tests**: 6 تست
- ✅ **Payment Tests**: 6 تست
- ✅ **Penalty Tests**: 3 تست
- ✅ **Wallet Tests**: 25 تست

## 🏗️ معماری

- **Backend**: Laravel 12 (PHP 8.2+) با Repository Pattern, Service Layer, Policies
- **Frontend**: React 19 (TypeScript 5.9) + DaisyUI + React Query
- **Database**: MySQL 8.0+ با migrations و seeders
- **Payment Gateway**: Zarinpal (Sandbox/Production)
- **Documentation**: Swagger/OpenAPI (L5-Swagger 8)

## 📁 ساختار پروژه

```
LoanManagementSystem/
├── web-api/          # Laravel Backend
├── client/           # React Frontend
├── documents/        # مستندات جامع
└── README.md         # این فایل
```

## 🔐 دسترسی‌ها

- **Admin Panel**: مدیریت وام‌ها، کاربران و کیف پول مشترک
- **User Panel**: درخواست وام، پرداخت اقساط و شارژ کیف پول

## 💡 تکنولوژی‌های استفاده شده

### Backend
- **Laravel** 12.x (PHP 8.2+)
- **Sanctum** 4.x برای احراز هویت
- **MySQL** 8.0+ Database
- **Zarinpal SDK** 2.x برای پرداخت
- **Pest** 3.x برای تست‌ها
- **L5-Swagger** 8.x برای مستندات API

### Frontend
- **React** 19.x
- **TypeScript** 5.9.x
- **Tailwind CSS** 4.x + **DaisyUI** 5.x
- **React Query** 5.x برای state management
- **Axios** 1.x برای HTTP requests
- **React Router** 7.x برای routing

## 📝 License

MIT License

---

**نسخه:** 1.0.0  
**تاریخ به‌روزرسانی:** 2025
