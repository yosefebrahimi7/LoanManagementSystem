# 🚀 سیستم پرداخت - Quick Start Guide

## 📍 خلاصه سریع

این سیستم یکپارچه‌سازی کامل با زرین‌پال برای پرداخت اقساط وام است.

---

## ⚡ شروع سریع

### 1️⃣ Backend Setup
```bash
cd web-api

# Install dependencies
composer install

# Setup database
php artisan migrate

# Start server
php artisan serve
```

### 2️⃣ Frontend Setup
```bash
cd client

# Install dependencies
npm install

# Start dev server
npm run dev
```

### 3️⃣ Configure .env
```env
# Zarinpal Config
ZARINPAL_MERCHANT_ID=00000000-0000-0000-0000-000000000000
ZARINPAL_SANDBOX=true
ZARINPAL_CALLBACK_URL=http://localhost:8000/api/payment/callback
FRONTEND_URL=http://localhost:5174
```

---

## 🎯 چطور کار می‌کند؟

```
1. کاربر → صفحه پرداخت /loan-payment/{id}
2. کلیک روی "پرداخت" برای یک قسط
3. Redirect به درگاه زرین‌پال
4. پرداخت با کارت تست:
   - Card: 6037991234567890
   - CVV2: 123
   - Expiry: 12/25
   - Pass: 1234
5. زرین‌پال برمی‌گرداند → callback API
6. Backend پرداخت را verify می‌کند
7. داده‌ها به‌روزرسانی می‌شود:
   - paid_amount در schedule
   - remaining_balance در loan
   - status به "paid" تغییر می‌کند
8. Redirect به صفحه success در فرانت ✅
```

---

## 🧪 تست کردن

### مرحله 1: ایجاد وام
```
1. لاگین شوید
2. /loan-request بروید
3. یک وام ایجاد کنید
4. با admin تایید کنید
```

### مرحله 2: پرداخت
```
1. /loan-payment/{id} بروید
2. روی "پرداخت" کلیک کنید
3. در sandbox پرداخت کنید
4. نتیجه را ببینید ✅
```

---

## 📂 فایل‌های مهم

### Backend
- `app/Services/ZarinpalService.php` - سرویس زرین‌پال
- `app/Http/Controllers/Api/PaymentController.php` - کنترلر پرداخت
- `routes/api.php` - Route‌ها

### Frontend
- `src/pages/LoanPayment.tsx` - صفحه پرداخت
- `src/pages/PaymentSuccess.tsx` - صفحه موفقیت
- `src/pages/PaymentFailed.tsx` - صفحه خطا
- `src/hooks/usePayments.ts` - Hook های پرداخت

---

## 🔗 Routes

### Protected Routes
- `POST /api/payment/loans/{loan}/initiate` - شروع پرداخت
- `GET /api/payment/history` - تاریخچه
- `GET /api/payment/status/{payment}` - وضعیت

### Public Routes
- `GET|POST /api/payment/callback` - دریافت callback از درگاه

---

## ✅ Checklist نهایی

بعد از انجام تست، باید:
- [ ] وام تایید شده باشد
- [ ] قسط‌ها نمایش داده شوند
- [ ] پرداخت موفق شود
- [ ] به صفحه success redirect شود
- [ ] وضعیت به "پرداخت شده" تغییر کند
- [ ] باقیمانده وام به‌روزرسانی شود

---

## 🐛 عیب‌یابی

### مشکل: Redirect به پورت اشتباه
```bash
php artisan config:clear
php artisan cache:clear
# سرور را restart کنید
```

### مشکل: "Too many attempts"
```bash
# 5 دقیقه صبر کنید
# دوباره تلاش کنید
```

### مشکل: وضعیت به‌روزرسانی نمی‌شود
```bash
# لاگ‌ها را ببینید
tail -f storage/logs/laravel.log
```

---

## 📖 مستندات کامل

برای جزئیات بیشتر:
- [مستندات کامل پرداخت](./payment-integration.md)
- [راهنمای تست](./payment-test-guide.md)
- [نمای کلی سیستم](./SYSTEM_OVERVIEW.md)

---

**آخرین بروزرسانی**: 26 اکتبر 2025

