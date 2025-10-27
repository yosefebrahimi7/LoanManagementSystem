# Loan Management System - API Documentation

## فهرست مطالب
- [نصب و راه‌اندازی](#نصب-و-راه‌اندازی)
- [پیکربندی محیط](#پیکربندی-محیط)
- [اجرای برنامه](#اجرای-برنامه)
- [API Endpoints](#api-endpoints)
- [تست‌ها](#تست‌ها)
- [ویژگی‌ها](#ویژگی‌ها)
- [توسعه](#توسعه)

---

## نصب و راه‌اندازی

### پیش‌نیازها
- PHP 8.2+
- Composer
- MySQL/MariaDB 8.0+
- Node.js 18+ (برای frontend)

### مراحل نصب

1. **Clone پروژه**
```bash
git clone <repository-url>
cd LoanManagementSystem/web-api
```

2. **نصب Dependencies**
```bash
composer install
```

3. **تنظیم Environment**
```bash
# کپی کردن فایل نمونه
cp ../documents/env.template .env

# ایجاد APP_KEY
php artisan key:generate
```

4. **تنظیم Database**

فایل `.env` را ویرایش کنید:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=loan_management
DB_USERNAME=root
DB_PASSWORD=
```

5. **اجرای Migrations و Seeders**
```bash
php artisan migrate --seed
```

6. **اجرای Server**
```bash
php artisan serve
```

سرویس در آدرس `http://localhost:8000` در دسترس خواهد بود.

---

## پیکربندی محیط

### تنظیمات پایگاه داده
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=loan_management
DB_USERNAME=root
DB_PASSWORD=
```

### تنظیمات ایمیل (SMTP)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

### تنظیمات Zarinpal
```env
ZARINPAL_MERCHANT_ID=your-merchant-id
ZARINPAL_SECRET_KEY=your-secret-key
ZARINPAL_SANDBOX=true
ZARINPAL_CALLBACK_URL=http://localhost:8000/api/payment/callback
```

### تنظیمات Swagger
```env
L5_SWAGGER_GENERATE_ALWAYS=true
L5_SWAGGER_USE_ABSOLUTE_PATH=true
L5_SWAGGER_BASE_PATH=http://localhost:8000
```

---

## اجرای برنامه

### اجرای Server
```bash
php artisan serve
```

### اجرای Queue Worker
برای ارسال ایمیل و job های background:
```bash
php artisan queue:work
```

### اجرای Scheduled Tasks
برای محاسبه جریمه‌های روزانه:
```bash
php artisan schedule:work
```

در production:
```bash
# اضافه کردن به crontab
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### دستورات کاربردی

```bash
# اجرای تست‌ها
php artisan test

# Generate Swagger docs
php artisan l5-swagger:generate

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# اجرای Command جریمه
php artisan loans:process-penalties
```

---

## API Endpoints

### مستندات Swagger
- **URL**: `http://localhost:8000/api/documentation`
- مشاهده تمام endpoints و تست آن‌ها به صورت interactive

### Authentication
- `POST /api/auth/register` - ثبت نام
- `POST /api/auth/login` - ورود
- `POST /api/auth/logout` - خروج
- `GET /api/auth/me` - دریافت اطلاعات کاربر
- `POST /api/auth/refresh` - تازه‌سازی توکن

### Loans
- `GET /api/loans` - دریافت لیست وام‌های کاربر
- `POST /api/loans` - ایجاد درخواست وام
- `GET /api/loans/{id}` - جزئیات وام

### Admin
- `GET /api/admin/loans` - لیست تمام وام‌ها
- `POST /api/admin/loans/{id}/approve` - تایید/رد وام
- `GET /api/admin/loans/stats` - آمار وام‌ها

### Payments
- `GET /api/payment/history` - تاریخچه پرداخت‌ها
- `POST /api/payment/loans/{loan}/initiate` - شروع پرداخت
- `GET /api/payment/status/{payment}` - وضعیت پرداخت
- `POST /api/payment/callback` - کال‌بک درگاه

---

## تست‌ها

پروژه شامل **15+ تست** است:
- **AuthTest**: 8 تست برای احراز هویت
- **LoanTest**: 3 تست برای وام‌ها
- **PenaltyTest**: 4 تست برای محاسبه جریمه

### اجرای تست‌ها
```bash
php artisan test
```

### دستورات تک‌تک
```bash
# تست احراز هویت
php artisan test --filter AuthTest

# تست وام‌ها
php artisan test --filter LoanTest

# تست جریمه
php artisan test --filter PenaltyTest
```

---

## ویژگی‌ها

- ✅ Authentication با Sanctum
- ✅ Loan Management (ایجاد، تایید، رد)
- ✅ Payment Schedule (جدول زمانی پرداخت)
- ✅ Penalty Calculation (محاسبه جریمه روزانه)
- ✅ Scheduled Commands
- ✅ Zarinpal Payment Gateway
- ✅ Wallet System
- ✅ Admin Panel API
- ✅ Swagger Documentation
- ✅ Comprehensive Tests (15+)
- ✅ Repository Pattern
- ✅ Service Layer
- ✅ Event-Driven Architecture

---

## ساختار پروژه

```
app/
├── Console/Commands/
│   ├── CleanupExpiredTokens.php
│   └── ProcessLoanPenalties.php
├── Events/
│   ├── InstallmentPaid.php
│   ├── LoanApproved.php
│   └── LoanRejected.php
├── Http/
│   ├── Controllers/Api/  # API Controllers
│   ├── Requests/         # Form Requests
│   ├── Resources/        # API Resources
│   └── Middleware/       # Middleware
├── Jobs/           # Background Jobs
├── Models/         # Eloquent Models
├── Policies/       # Authorization Policies
├── Repositories/   # Repository Pattern
├── Services/       # Business Logic
└── Traits/         # Reusable Traits
```

---

## حساب‌های پیش‌فرض

بعد از اجرای `php artisan db:seed`:

- **Admin**: `admin@example.com` / `password`
- **User 1**: `test1@example.com` / `password`
- **User 2**: `test2@example.com` / `password`

**مجموع:** 1 ادمین + 2 کاربر

---

## توسعه

### افزودن تست جدید
```bash
php artisan make:test Feature/YourTest
```

### ایجاد Migration
```bash
php artisan make:migration create_table_name
php artisan migrate
```

### ایجاد Model
```bash
php artisan make:model ModelName
```

### ساخت Controller
```bash
php artisan make:controller Api/YourController --api
```

### ایجاد Service
```bash
php artisan make:service YourService
```

---

## Penalty Calculation

سیستم به صورت خودکار روزانه جریمه‌های قسط‌های معوقه را محاسبه می‌کند:

- **Command**: `php artisan loans:process-penalties`
- **Schedule**: هر روز در ساعت 00:00
- **Rate**: قابل تنظیم از `settings` table (default: 0.5% per day)

---

## License

MIT License

