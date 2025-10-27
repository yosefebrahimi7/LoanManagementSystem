# نمای کلی سیستم مدیریت وام

## 📌 فهرست مطالب
1. [معرفی](#معرفی)
2. [تکنولوژی‌های استفاده شده](#تکنولوژی-های-استفاده-شده)
3. [معماری پروژه](#معماری-پروژه)
4. [فهرست قابلیت‌ها](#قابلیت-ها)
5. [ساختار پروژه](#ساختار-پروژه)

---

## 🎯 معرفی

سیستم مدیریت وام یک سیستم کامل برای مدیریت وام‌های بانکی است که شامل:

- 🔐 احراز هویت کامل
- 💰 مدیریت کیف پول
- 📋 درخواست و مدیریت وام
- 💳 پرداخت اقساط با زرین‌پال
- 📊 داشبورد آماری
- 👥 مدیریت کاربران

---

## 💻 تکنولوژی‌های استفاده شده

### Backend
- **Laravel 12** - Framework اصلی
- **PHP 8.2** - زبان برنامه‌نویسی
- **MySQL** - دیتابیس
- **Sanctum** - Authentication
- **Zarinpal SDK** - درگاه پرداخت

### Frontend
- **React 18** - کتابخانه اصلی
- **TypeScript** - Type safety
- **React Router** - Navigation
- **TanStack Query** - State management
- **DaisyUI** - UI Components
- **TailwindCSS** - Styling

---

## 🏗️ معماری پروژه

```
LoanManagementSystem/
├── web-api/          # Backend (Laravel)
│   ├── app/
│   │   ├── Http/Controllers/
│   │   ├── Services/
│   │   ├── Models/
│   │   └── Exceptions/
│   ├── routes/
│   └── database/
│
└── client/           # Frontend (React)
    ├── src/
    │   ├── pages/
    │   ├── components/
    │   ├── hooks/
    │   └── utils/
    └── public/
```

---

## ✨ قابلیت‌ها

### 🔐 احراز هویت
- ✅ ثبت‌نام
- ✅ ورود / خروج
- ✅ Token-based authentication
- ✅ Refresh token

### 💰 کیف پول
- ✅ ایجاد کیف پول خودکار
- ✅ موجودی
- ✅ تراکنش‌ها

### 📋 مدیریت وام
- ✅ درخواست وام
- ✅ تایید/رد وام (Admin)
- ✅ برنامه پرداخت اقساط
- ✅ وضعیت‌های مختلف: pending, approved, rejected, active, delinquent, paid

### 💳 پرداخت
- ✅ پرداخت اقساط با زرین‌پال
- ✅ Sandbox و Production
- ✅ به‌روزرسانی خودکار
- ✅ تاریخچه پرداخت‌ها

### 📊 داشبورد
- ✅ آمار وام‌ها
- ✅ فیلتر بر اساس وضعیت
- ✅ مدیریت کاربران (Admin)

---

## 📁 ساختار پروژه

### Backend Structure

```
web-api/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   ├── AuthController.php
│   │   │   ├── LoanController.php
│   │   │   └── PaymentController.php
│   │   ├── Requests/
│   │   ├── Resources/
│   │   └── Middleware/
│   ├── Services/
│   │   ├── AuthService.php
│   │   ├── LoanService.php
│   │   ├── LoanStatisticsService.php
│   │   └── ZarinpalService.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Loan.php
│   │   ├── LoanSchedule.php
│   │   ├── LoanPayment.php
│   │   └── Wallet.php
│   ├── Repositories/
│   └── Exceptions/
│
├── routes/
│   └── api.php
│
└── database/
    ├── migrations/
    ├── seeders/
    └── factories/
```

### Frontend Structure

```
client/
├── src/
│   ├── pages/
│   │   ├── Dashboard.tsx
│   │   ├── LoanRequest.tsx
│   │   ├── LoanDetails.tsx
│   │   ├── LoanPayment.tsx
│   │   ├── PaymentSuccess.tsx
│   │   ├── PaymentFailed.tsx
│   │   └── ...
│   ├── components/
│   │   ├── Header.tsx
│   │   ├── Layout.tsx
│   │   └── ProtectedRoute.tsx
│   ├── hooks/
│   │   ├── useAuth.ts
│   │   ├── useLoans.ts
│   │   ├── usePayments.ts
│   │   └── ...
│   ├── stores/
│   │   └── auth.ts
│   └── utils/
│       └── loanStatus.ts
│
└── public/
```

---

## 🔄 جریان‌های اصلی

### 1. جریان درخواست وام
```
کاربر → درخواست وام → Admin تایید → آماده پرداخت
```

### 2. جریان پرداخت
```
کاربر → صفحه پرداخت → زرین‌پال → تایید → به‌روزرسانی
```

### 3. جریان مدیریت
```
Admin → لیست وام‌ها → فیلتر → تایید/رد
```

---

## 📚 مستندات کامل

- [📘 راهنمای نصب](./installation.md)
- [📘 طراحی دیتابیس](./database-design.md)
- [📘 مستندات API](./api-documentation.md)
- [📘 سیستم پرداخت](./payment-integration.md)
- [📘 راهنمای تست](./payment-test-guide.md)
- [📘 راهنمای توسعه](./development.md)

---

## 🚀 شروع سریع

### 1. نصب Backend
```bash
cd web-api
composer install
php artisan migrate
php artisan serve
```

### 2. نصب Frontend
```bash
cd client
npm install
npm run dev
```

### 3. تست سیستم
```bash
# تست احراز هویت
POST http://localhost:8000/api/auth/register
POST http://localhost:8000/api/auth/login

# تست وام
POST http://localhost:8000/api/loans

# تست پرداخت
POST http://localhost:8000/api/payment/loans/{id}/initiate
```

---

## 🔧 تنظیمات مهم

### Environment Variables

#### Backend (.env)
```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=loan_management
DB_USERNAME=root
DB_PASSWORD=

# Zarinpal
ZARINPAL_MERCHANT_ID=00000000-0000-0000-0000-000000000000
ZARINPAL_SANDBOX=true
ZARINPAL_CALLBACK_URL=http://localhost:8000/api/payment/callback
FRONTEND_URL=http://localhost:5174
```

---

## 🎨 UI/UX Features

### طراحی
- ✅ RTL Support
- ✅ Responsive Design
- ✅ Persian Fonts (Yekan Bakh)
- ✅ Modern UI با DaisyUI

### وضعیت‌ها
- **pending**: نارنجی - در انتظار تایید
- **approved**: آبی - تایید شده
- **rejected**: قرمز - رد شده
- **active**: سبز - فعال
- **delinquent**: قرمز - معوق
- **paid**: خاکستری - پرداخت شده

---

## 🧪 Test Cards (Sandbox)

```
شماره کارت: 6037991234567890
CVV2: 123
Expiry: 12/25
Password: 1234
```

---

## 📊 Status Management

### وام‌ها
| وضعیت | رنگ | توضیحات |
|-------|-----|---------|
| pending | نارنجی | در انتظار تایید Admin |
| approved | آبی | تایید شده، آماده پرداخت |
| rejected | قرمز | رد شده توسط Admin |
| active | سبز | فعال، در حال پرداخت |
| delinquent | قرمز | معوق (باقیمانده) |
| paid | خاکستری | تمام شده |

### قسط‌ها
| وضعیت | رنگ | توضیحات |
|-------|-----|---------|
| pending | نارنجی | در انتظار پرداخت |
| partial | زرد | جزئی پرداخت شده |
| paid | سبز | پرداخت کامل |
| overdue | قرمز | سررسید گذشته |

---

## 🎯 نکات مهم

### امنیت
- ✅ همه API ها محافظت شده هستند
- ✅ از Sanctum برای authentication استفاده می‌شود
- ✅ Validation در همه نقاط
- ✅ Transaction برای consistency

### Performance
- ✅ Caching برای queries
- ✅ Pagination برای لیست‌ها
- ✅ Optimized queries
- ✅ Lazy loading relationships

---

## 📞 پشتیبانی

برای هرگونه سوال یا مشکل:
1. لاگ‌ها را بررسی کنید: `storage/logs/laravel.log`
2. مستندات را مطالعه کنید
3. با تیم توسعه تماس بگیرید

---

**آخرین بروزرسانی**: 26 اکتبر 2025  
**نسخه**: 1.0.0

