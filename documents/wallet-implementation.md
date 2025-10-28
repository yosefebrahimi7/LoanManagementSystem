# Wallet Feature Implementation

## Overview

این سند توضیح می‌دهد که چگونه قابلیت کیف پول برای سیستم مدیریت وام پیاده‌سازی شده است.

## ویژگی‌های پیاده‌سازی شده

### 1. کیف پول مشترک ادمین (Shared Admin Wallet)
- یک کیف پول مشترک برای تمام ادمین‌ها
- ادمین‌ها می‌توانند این کیف پول را شارژ کنند
- ادمین‌ها می‌توانند تراکنش‌های این کیف پول را ببینند

### 2. کیف پول کاربری
- هر کاربر یک کیف پول مخصوص خود دارد
- کاربران می‌توانند کیف پول خود را شارژ کنند
- کاربران می‌توانند تراکنش‌های خود را مشاهده کنند

### 3. شارژ از طریق درگاه زرین‌پال
- امکان شارژ کیف پول از طریق درگاه پرداخت زرین‌پال
- حداقل مبلغ: ۱۰,۰۰۰ تومان
- حداکثر مبلغ: ۱,۰۰۰,۰۰۰ تومان

## معماری

### Backend (Laravel)

#### 1. Model
**فایل:** `web-api/app/Models/Wallet.php`
- اضافه شدن فیلد `is_shared` برای تشخیص کیف پول مشترک
- متد `isAdminSharedWallet()` برای بررسی کیف پول مشترک
- Scope `sharedAdminWallet()` برای جستجوی کیف پول مشترک

#### 2. Repository
**فایل:** `web-api/app/Repositories/WalletRepository.php`
- متد `getSharedAdminWallet()` برای دریافت کیف پول مشترک
- متد `getOrCreateSharedAdminWallet()` برای ایجاد یا دریافت کیف پول مشترک

#### 3. Service
**فایل:** `web-api/app/Services/WalletService.php`
- متد `getWallet()` - دریافت کیف پول کاربر یا ادمین بر اساس نقش
- متد `getTransactions()` - دریافت تراکنش‌های کیف پول
- متد `initiateRecharge()` - شروع فرآیند شارژ
- متد `processRechargeCallback()` - پردازش بازگشت از درگاه پرداخت

#### 4. Controller
**فایل:** `web-api/app/Http/Controllers/Api/WalletController.php`
- `GET /api/wallet` - دریافت موجودی کیف پول
- `GET /api/wallet/transactions` - دریافت تراکنش‌ها
- `POST /api/wallet/recharge` - شروع شارژ کیف پول
- `GET /api/wallet/callback` - کال‌بک از درگاه پرداخت

#### 5. Request Validation
**فایل:** `web-api/app/Http/Requests/WalletRechargeRequest.php`
- بررسی وجود مبلغ
- بررسی حداقل و حداکثر مبلغ
- پیام‌های خطای فارسی

#### 6. Policy
**فایل:** `web-api/app/Policies/WalletPolicy.php`
- کاربران فقط می‌توانند کیف پول خود را ببینند
- ادمین‌ها می‌توانند کیف پول مشترک را ببینند
- ادمین‌ها می‌توانند کیف پول مشترک را شارژ کنند

#### 7. Migration
**فایل:** `web-api/database/migrations/2025_10_28_014423_add_is_shared_to_wallets_table.php`
- اضافه کردن فیلد `is_shared` به جدول `wallets`

#### 8. Seeder
**فایل:** `web-api/database/seeders/SharedAdminWalletSeeder.php`
- ایجاد کیف پول مشترک ادمین در صورت عدم وجود

### Frontend (React + TypeScript)

#### 1. Wallet Dropdown Component
**فایل:** `client/src/components/WalletDropdown.tsx`
- نمایش موجودی کیف پول
- نمایش آخرین تراکنش‌ها
- دکمه شارژ کیف پول
- مودال برای وارد کردن مبلغ شارژ

#### 2. Header Component
**فایل:** `client/src/components/Header.tsx`
- اضافه شدن آیکن کیف پول در کنار آیکن اعلان‌ها

#### 3. Wallet Hooks
**فایل:** `client/src/hooks/useWallet.ts`
- `useWallet()` - دریافت موجودی کیف پول
- `useWalletTransactions()` - دریافت تراکنش‌ها
- `useAddToWallet()` - شارژ کیف پول

## نحوه استفاده

### 1. نصب و راه‌اندازی

```bash
# نصب وابستگی‌ها
cd web-api
composer install
npm install

# اجرای مایگریشن‌ها
php artisan migrate

# اجرای سیدرها
php artisan db:seed

# راه‌اندازی سرویس‌ها
cd ../client
npm install
npm run dev
```

### 2. شارژ کیف پول

#### برای کاربران عادی:
1. روی آیکن کیف پول در هدر کلیک کنید
2. روی دکمه "شارژ کیف پول" کلیک کنید
3. مبلغ مورد نظر را وارد کنید (حداقل ۱۰,۰۰۰ تومان)
4. روی "افزودن به کیف پول" کلیک کنید
5. به درگاه پرداخت زرین‌پال منتقل می‌شوید
6. پس از پرداخت، به سیستم بازمی‌گردید و موجودی به‌روزرسانی می‌شود

#### برای ادمین‌ها:
- همین مراحل را دنبال کنید
- کیف پول شارژ شده، کیف پول مشترک ادمین‌ها است

### 3. مشاهده تراکنش‌ها
- روی آیکن کیف پول کلیک کنید
- لیست آخرین تراکنش‌ها نمایش داده می‌شود
- برای مشاهده همه تراکنش‌ها، می‌توانید API را مستقیماً صدا بزنید

## API Endpoints

### GET /api/wallet
دریافت موجودی کیف پول

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "balance": 5000000,
    "formatted_balance": "50,000.00",
    "currency": "IRR",
    "is_shared": false
  }
}
```

### GET /api/wallet/transactions
دریافت تراکنش‌های کیف پول

**Query Parameters:**
- `page` - شماره صفحه (پیش‌فرض: 1)
- `limit` - تعداد در هر صفحه (پیش‌فرض: 10)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "type": "credit",
      "amount": 100000,
      "balance_after": 5100000,
      "description": "شارژ کیف پول",
      "created_at": "2024-01-01T12:00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 10,
    "total": 1
  }
}
```

### POST /api/wallet/recharge
شروع فرآیند شارژ

**Request Body:**
```json
{
  "amount": 100000
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "payment_url": "https://sandbox.zarinpal.com/pg/StartPay/...",
    "authority": "A00000000000000000000000000000000000",
    "transaction_id": 1
  }
}
```

## پترن‌های معماری استفاده شده

### 1. Repository Pattern
استفاده از Repository Pattern برای جداسازی منطق دسترسی به داده‌ها

### 2. Service Pattern
استفاده از Service Pattern برای جداسازی منطق تجاری

### 3. Policy Pattern
استفاده از Laravel Policies برای کنترل دسترسی

### 4. Form Request Validation
استفاده از Form Request برای اعتبارسنجی ورودی‌ها

## تست‌ها

تست‌های جامع در فایل `web-api/tests/Feature/WalletTest.php` نوشته شده‌اند:

- تست ایجاد کیف پول برای کاربر جدید
- تست ایجاد کیف پول مشترک ادمین
- تست دریافت موجودی کیف پول
- تست دریافت تراکنش‌ها
- تست شروع فرآیند شارژ
- تست اعتبارسنجی مبلغ شارژ
- تست استفاده ادمین‌ها از کیف پول مشترک
- تست دسترسی‌ها (Policy Tests)

## نکات مهم

1. **کیف پول مشترک**: فقط یک کیف پول مشترک برای همه ادمین‌ها وجود دارد
2. **حداقل و حداکثر مبلغ**: ۱۰,۰۰۰ تا ۱,۰۰۰,۰۰۰ تومان
3. **ذخیره‌سازی موجودی**: موجودی به ریال ذخیره می‌شود (در API به تومان فرستاده می‌شود)
4. **کش‌گذاری**: اطلاعات کیف پول به مدت ۳۰۰ ثانیه کش می‌شوند
5. **Callback URL**: `/api/wallet/callback`

## توسعه‌های آتی

1. برداشت از کیف پول
2. انتقال موجودی بین کیف پول‌ها
3. گزارش‌های جامع تراکنش‌ها
4. هشدارهای موجودی کم
5. لیمیت شارژ روزانه

