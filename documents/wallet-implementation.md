# Wallet Feature Implementation

## Overview

این سند توضیح می‌دهد که چگونه قابلیت کیف پول برای سیستم مدیریت وام پیاده‌سازی شده است.

## ویژگی‌های پیاده‌سازی شده

### 1. کیف پول مشترک ادمین (Shared Admin Wallet)
- یک کیف پول واحد برای تمام ادمین‌ها
- ادمین‌ها می‌توانند این کیف پول را شارژ کنند
- ادمین‌ها می‌توانند تراکنش‌های این کیف پول را ببینند
- در زمان اجرا، اولین ادمینی که لاگین می‌کند این کیف پول را دریافت می‌کند
- اگر کیف پول مشترک موجود نباشد، به طور خودکار ایجاد می‌شود

### 2. کیف پول کاربری
- هر کاربر عادی کیف پول مخصوص خودش را دارد
- کاربران می‌توانند کیف پول خود را شارژ کنند
- کاربران می‌توانند تراکنش‌های خود را مشاهده کنند

### 3. شارژ از طریق درگاه زرین‌پال
- امکان شارژ کیف پول از طریق درگاه پرداخت زرین‌پال
- حداقل مبلغ: ۱۰,۰۰۰ تومان
- حداکثر مبلغ: ۲,۰۰۰,۰۰۰,۰۰۰ تومان (۲ میلیارد تومان)

### 4. UI/UX زیبا
- آیکن کیف پول در Header کنار آیکن اعلان‌ها
- نمایش موجودی به صورت خوانا
- نمایش آخرین تراکنش‌ها در منوی دراپ‌داون
- مودال شارژ با اعتبارسنجی و نمایش مبلغ به فارسی
- هدایت خودکار پس از پرداخت موفق به صفحه موفقیت
- هدایت به صفحه خطا در صورت عدم پرداخت

## معماری

### Backend (Laravel)

#### 1. Model
**فایل:** `web-api/app/Models/Wallet.php`
- اضافه شدن فیلد `is_shared` برای تشخیص کیف پول مشترک
- متد `isAdminSharedWallet()` برای بررسی کیف پول مشترک
- Scope `sharedAdminWallet()` برای جستجوی کیف پول مشترک
- Scope `userWallets()` برای جستجوی کیف پول‌های کاربری

#### 2. Repository
**فایل:** `web-api/app/Repositories/WalletRepository.php`
- متد `getSharedAdminWallet()` برای دریافت کیف پول مشترک
- متد `getOrCreateSharedAdminWallet()` برای ایجاد یا دریافت کیف پول مشترک
- متدهای استاندارد CRUD
- Cache برای بهبود عملکرد

#### 3. Service
**فایل:** `web-api/app/Services/WalletService.php`
- متد `getWallet()` - دریافت کیف پول کاربر یا ادمین بر اساس نقش
- متد `getTransactions()` - دریافت تراکنش‌های کیف پول با pagination
- متد `initiateRecharge()` - شروع فرآیند شارژ با تبدیل واحدها
- متد `processRechargeCallback()` - پردازش بازگشت از درگاه پرداخت
- متد `completeRecharge()` - تکمیل شارژ و بروزرسانی موجودی
- متد `failRecharge()` - ثبت شارژ ناموفق
- متد `translateErrorMessage()` - ترجمه پیام‌های خطا به فارسی

#### 4. Controller
**فایل:** `web-api/app/Http/Controllers/Api/WalletController.php`
- `GET /api/wallet` - دریافت موجودی کیف پول
- `GET /api/wallet/transactions` - دریافت تراکنش‌ها با pagination
- `POST /api/wallet/recharge` - شروع شارژ کیف پول
- `POST /api/wallet/callback` - کال‌بک از درگاه پرداخت
- مستندات کامل Swagger/OpenAPI

#### 5. Request Validation
**فایل:** `web-api/app/Http/Requests/WalletRechargeRequest.php`
- بررسی وجود مبلغ (required)
- بررسی نوع عدد (integer)
- بررسی حداقل مبلغ (10,000 تومان)
- بررسی حداکثر مبلغ (2,000,000,000 تومان)
- پیام‌های خطای فارسی

#### 6. Policy
**فایل:** `web-api/app/Policies/WalletPolicy.php`
- کاربران فقط می‌توانند کیف پول خود را ببینند و شارژ کنند
- ادمین‌ها می‌توانند کیف پول مشترک را ببینند و شارژ کنند
- ادمین‌ها می‌توانند تراکنش‌های کیف پول مشترک را ببینند
- فقط ادمین‌ها می‌توانند کیف پول را حذف کنند

#### 7. Migration
**فایلها:**
- `web-api/database/migrations/2025_10_28_014423_add_is_shared_to_wallets_table.php`
  - اضافه کردن فیلد `is_shared` به جدول `wallets`
- `web-api/database/migrations/2025_10_28_015350_modify_wallets_user_id_nullable.php`
  - تغییر فیلد `user_id` به nullable برای کیف پول مشترک

#### 8. Seeder
**فایل:** `web-api/database/seeders/SharedAdminWalletSeeder.php`
- ایجاد کیف پول مشترک ادمین در صورت عدم وجود
- اجرای خودکار در `DatabaseSeeder`

#### 9. Registration
**فایل:** `web-api/app/Providers/AppServiceProvider.php`
- ثبت `WalletService` در Service Container
- ثبت `WalletPolicy` برای کنترل دسترسی‌ها

### Frontend (React + TypeScript)

#### 1. Wallet Dropdown Component
**فایل:** `client/src/components/WalletDropdown.tsx`
- نمایش موجودی کیف پول
- نمایش آخرین تراکنش‌ها (5 عدد)
- دکمه شارژ کیف پول
- مودال برای وارد کردن مبلغ شارژ
- نمایش مبلغ به صورت حروف فارسی
- عدم نمایش "تومان" تکراری
- محدودیت ورودی: min=10,000, max=2,000,000,000

#### 2. Header Component
**فایل:** `client/src/components/Header.tsx`
- اضافه شدن آیکن کیف پول در کنار آیکن اعلان‌ها
- `UserDropdown` به عنوان کامپوننت جداگانه

#### 3. User Dropdown Component
**فایل:** `client/src/components/UserDropdown.tsx`
- نمایش آواتار کاربر
- نمایش نام و ایمیل کاربر
- دسترسی سریع به پروفایل
- دسترسی به مدیریت کاربران (برای ادمین)
- دکمه خروج

#### 4. Pages
**فایلها:**
- `client/src/pages/WalletRechargeSuccess.tsx` - صفحه موفقیت شارژ
- `client/src/pages/WalletRechargeFailed.tsx` - صفحه خطای شارژ

#### 5. Hooks
**فایل:** `client/src/hooks/useWallet.ts`
- `useWallet()` - دریافت موجودی کیف پول
- `useWalletTransactions()` - دریافت تراکنش‌ها با pagination
- `useAddToWallet()` - شارژ کیف پول

#### 6. Routes
**فایل:** `client/src/App.tsx`
- `/wallet/recharge/success` - صفحه موفقیت
- `/wallet/recharge/failed` - صفحه خطا

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

# Generate Swagger documentation
php artisan l5-swagger:generate

# راه‌اندازی backend
php artisan serve

# راه‌اندازی frontend
cd ../client
npm install
npm run dev
```

### 2. شارژ کیف پول

#### برای کاربران عادی:
1. روی آیکن کیف پول در Header کلیک کنید
2. موجودی فعلی را مشاهده کنید
3. روی دکمه "شارژ کیف پول" کلیک کنید
4. مبلغ مورد نظر را وارد کنید (حداقل ۱۰,۰۰۰ تومان، حداکثر ۲,۰۰۰,۰۰۰,۰۰۰ تومان)
5. مبلغ را به صورت حروف فارسی در زیر input مشاهده کنید
6. روی "افزودن به کیف پول" کلیک کنید
7. به درگاه پرداخت زرین‌پال منتقل می‌شوید
8. پرداخت را انجام دهید
9. پس از پرداخت موفق، به صفحه موفقیت هدایت می‌شوید
10. موجودی به‌روزرسانی می‌شود و تراکنش ثبت می‌شود

#### برای ادمین‌ها:
- همین مراحل را دنبال کنید
- کیف پول شارژ شده، کیف پول مشترک ادمین‌ها است

### 3. مشاهده تراکنش‌ها
- روی آیکن کیف پول کلیک کنید
- آخرین 5 تراکنش نمایش داده می‌شود
- برای مشاهده همه تراکنش‌ها، می‌توانید API را مستقیماً صدا بزنید

## API Endpoints

### GET /api/wallet
**Description:** دریافت موجودی کیف پول

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "balance": 0,
    "formatted_balance": "0.00",
    "currency": "IRR",
    "is_shared": false
  }
}
```

### GET /api/wallet/transactions
**Description:** دریافت تراکنش‌های کیف پول

**Query Parameters:**
- `page` (integer, optional): شماره صفحه (پیش‌فرض: 1)
- `limit` (integer, optional): تعداد در هر صفحه (پیش‌فرض: 10)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "type": "credit",
      "amount": 10000000,
      "balance_after": 10000000,
      "description": "شارژ کیف پول",
      "created_at": "2024-01-01T12:00:00.000000Z"
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
**Description:** شروع فرآیند شارژ کیف پول

**Request Body:**
```json
{
  "amount": 1000000,
  "method": "zarinpal"
}
```

**Validation:**
- `amount`: required, integer, min:10000, max:2000000000

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

### POST /api/wallet/callback
**Description:** پردازش بازگشت از درگاه پرداخت (Public route)

**Request Parameters:**
- `Authority`: Reference کد از زرین‌پال
- `Status`: وضعیت پرداخت (OK, NOK)

**Response:**
Redirect to success or failed page

## پترن‌های معماری استفاده شده

### 1. Repository Pattern
استفاده از Repository Pattern برای جداسازی منطق دسترسی به داده‌ها

**Interface:** `App\Repositories\Interfaces\WalletRepositoryInterface`
**Implementation:** `App\Repositories\WalletRepository`

### 2. Service Pattern
استفاده از Service Pattern برای جداسازی منطق تجاری

**Interface:** `App\Services\Interfaces\WalletServiceInterface`
**Implementation:** `App\Services\WalletService`

### 3. Policy Pattern
استفاده از Laravel Policies برای کنترل دسترسی

**File:** `App\Policies\WalletPolicy`

### 4. Form Request Validation
استفاده از Form Request برای اعتبارسنجی ورودی‌ها

**File:** `App\Http\Requests\WalletRechargeRequest`

### 5. Factory Pattern
استفاده از Factory برای ساخت اشیاء

**File:** `app/Database/Factories/WalletFactory.php`

## واحدهای پولی

### واحد ذخیره‌سازی در Database
- **Wallet balance**: ریال (Rials)
- **Wallet transactions**: ریال (Rials)

### واحد ورودی/خروجی API
- **User input**: تومان (Tomans)
- **API requests**: تومان (Tomans)
- **Zarinpal payment gateway**: ریال (Rials)

### تبدیل واحدها
```
1 Toman = 10 Rials
Example: 1,000,000 تومان = 10,000,000 ریال
```

**فرآیند شارژ:**
1. کاربر: 1,000,000 تومان وارد می‌کند
2. Frontend: 1,000,000 تومان به Backend می‌فرستد
3. Backend validation: بررسی 10,000 تا 2,000,000,000 تومان
4. WalletService: تبدیل به 10,000,000 ریال
5. Database: 10,000,000 ریال ذخیره می‌شود
6. Zarinpal: 10,000,000 ریال به درگاه فرستاده می‌شود
7. پس از تایید: موجودی کیف پول +10,000,000 ریال می‌شود
8. Frontend: 1,000,000 تومان نمایش داده می‌شود

## دیتابیس Schema

### جدول wallets
```sql
CREATE TABLE wallets (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NULL, -- NULL for shared admin wallet
    balance BIGINT DEFAULT 0, -- in Rials
    currency VARCHAR(3) DEFAULT 'IRR',
    is_shared BOOLEAN DEFAULT false,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX user_id (user_id)
);
```

### جدول wallet_transactions
```sql
CREATE TABLE wallet_transactions (
    id BIGINT PRIMARY KEY,
    wallet_id BIGINT NOT NULL,
    type ENUM('credit', 'debit'),
    amount BIGINT NOT NULL, -- in Rials
    balance_after BIGINT NOT NULL,
    description TEXT,
    meta JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON DELETE CASCADE,
    INDEX wallet_id (wallet_id)
);
```

## تست‌ها

**فایل:** `web-api/tests/Feature/WalletTest.php`

### تست‌های پیاده‌سازی شده:
1. ✅ تست ایجاد کیف پول برای کاربر جدید
2. ✅ تست ایجاد کیف پول مشترک ادمین
3. ✅ تست دریافت موجودی کیف پول
4. ✅ تست دریافت تراکنش‌ها
5. ✅ تست شروع فرآیند شارژ
6. ✅ تست اعتبارسنجی مبلغ شارژ
7. ✅ تست استفاده ادمین‌ها از کیف پول مشترک
8. ✅ تست دسترسی‌ها (Policy Tests)
9. ✅ تست کیف پول شخصی برای کاربران

## محدودیت‌ها

### مبلغ شارژ
- **حداقل:** 10,000 تومان (100,000 ریال)
- **حداکثر:** 2,000,000,000 تومان (20,000,000,000 ریال)

### محدودیت‌های زرین‌پال
Zarinpal حداکثر 20 میلیارد ریال را می‌پذیرد که معادل 2 میلیارد تومان است.

### سیاست دسترسی
- کاربران فقط می‌توانند کیف پول خود را ببینند و شارژ کنند
- ادمین‌ها فقط می‌توانند کیف پول مشترک را ببینند و شارژ کنند
- ادمین‌ها به تمام تراکنش‌های کیف پول مشترک دسترسی دارند

## توسعه‌های آتی

1. برداشت از کیف پول
2. انتقال موجودی بین کیف پول‌ها
3. گزارش‌های جامع تراکنش‌ها
4. هشدارهای موجودی کم
5. لیمیت شارژ روزانه
6. لیمیت برداشت روزانه
7. پردازش خودکار شارژ با کارت اعتباری
8. سیستم ارجاع و جوایز

## Known Issues

هیچ مشکل شناخته‌شده‌ای وجود ندارد.

## Changelog

### Version 1.0.0 (2024-01-01)
- ✅ پیاده‌سازی کیف پول مشترک ادمین
- ✅ پیاده‌سازی کیف پول کاربری
- ✅ شارژ از طریق درگاه زرین‌پال
- ✅ UI/UX کاملاً فارسی
- ✅ تست‌های جامع
- ✅ مستندات Swagger
- ✅ مدیریت واحدهای پولی (تومان و ریال)
- ✅ محدودیت‌های شارژ (حداقل و حداکثر)
- ✅ صفحات موفقیت و خطا
- ✅ تراکنش‌های کامل با تاریخچه

## فایل‌های تغییر یافته/ایجاد شده

### Backend
1. `app/Models/Wallet.php` - اضافه شدن فیلد is_shared و متدها
2. `app/Models/WalletTransaction.php` - بدون تغییر
3. `app/Repositories/WalletRepository.php` - متدهای جدید
4. `app/Repositories/Interfaces/WalletRepositoryInterface.php` - متدهای جدید
5. `app/Services/WalletService.php` - ایجاد شده با تمام متدها
6. `app/Services/Interfaces/WalletServiceInterface.php` - ایجاد شده
7. `app/Http/Controllers/Api/WalletController.php` - ایجاد شده با Swagger
8. `app/Http/Requests/WalletRechargeRequest.php` - ایجاد شده
9. `app/Policies/WalletPolicy.php` - به‌روز شده
10. `app/Providers/AppServiceProvider.php` - ثبت سرویس و Policy
11. `routes/api.php` - اضافه کردن routes
12. `database/migrations/2025_10_28_014423_add_is_shared_to_wallets_table.php` - ایجاد شده
13. `database/migrations/2025_10_28_015350_modify_wallets_user_id_nullable.php` - ایجاد شده
14. `database/seeders/SharedAdminWalletSeeder.php` - ایجاد شده
15. `tests/Feature/WalletTest.php` - تست‌های جامع

### Frontend
1. `client/src/components/WalletDropdown.tsx` - ایجاد شده
2. `client/src/components/UserDropdown.tsx` - ایجاد شده
3. `client/src/components/Header.tsx` - اضافه شدن WalletDropdown
4. `client/src/pages/WalletRechargeSuccess.tsx` - ایجاد شده
5. `client/src/pages/WalletRechargeFailed.tsx` - ایجاد شده
6. `client/src/hooks/useWallet.ts` - به‌روز شده
7. `client/src/utils/numberUtils.ts` - اضافه شدن formatCurrency
8. `client/src/App.tsx` - اضافه شدن routes

### Documents
1. `documents/wallet-implementation.md` - این فایل
2. `documents/wallet-status.md` - وضعیت پیاده‌سازی
