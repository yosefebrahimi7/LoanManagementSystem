# وضعیت ویژگی کیف پول

## پیاده‌سازی قبلی

ویژگی کیف پول در این سیستم قبلاً پیاده‌سازی شده بود. این ویژگی شامل موارد زیر است:

### 1. ساختار پایه‌ای
- **Model Wallet** (`web-api/app/Models/Wallet.php`)
- **Model WalletTransaction** (`web-api/app/Models/WalletTransaction.php`)
- **Migration** برای جدول `wallets` و `wallet_transactions`
- **Repository Pattern** (`WalletRepository` و `WalletRepositoryInterface`)
- **Policy** برای کنترل دسترسی (`WalletPolicy`)

### 2. ویژگی‌های موجود
- ✅ دریافت موجودی کیف پول کاربر
- ✅ ثبت تراکنش‌های کیف پول
- ✅ نمایش تاریخچه تراکنش‌ها
- ✅ مدیریت موجودی به صورت ریال (کوچک‌ترین واحد پولی)

### 3. توسعه‌های جدید

#### Backend (Laravel)
- ✅ اضافه شدن فیلد `is_shared` برای پشتیبانی از کیف پول مشترک ادمین
- ✅ تغییر فیلد `user_id` به nullable برای امکان ایجاد کیف پول مشترک
- ✅ Service Layer (`WalletService` و `WalletServiceInterface`)
- ✅ Controller برای API endpoints (`WalletController`)
- ✅ Request Validation (`WalletRechargeRequest`)
- ✅ یکپارچه‌سازی با درگاه پرداخت زرین‌پال برای شارژ
- ✅ Policy به‌روزرسانی شده برای پشتیبانی از کیف پول مشترک
- ✅ Routes جدید برای wallet APIs
- ✅ Seeder برای ایجاد کیف پول مشترک ادمین

#### Frontend (React + TypeScript)
- ✅ کامپوننت `WalletDropdown` با قابلیت نمایش موجودی و تراکنش‌ها
- ✅ مودال شارژ کیف پول
- ✅ Hook‌های `useWallet` برای مدیریت state
- ✅ ادغام آیکن کیف پول در Header
- ✅ نمایش اعداد به فارسی در UI

### 4. ویژگی‌های جدید کیف پول مشترک

#### کیف پول مشترک ادمین‌ها
- یک کیف پول واحد برای تمام ادمین‌ها
- همه ادمین‌ها می‌توانند این کیف پول را شارژ کنند
- تمام ادمین‌ها می‌توانند تراکنش‌های این کیف پول را مشاهده کنند
- در زمان اجرا، اولین ادمینی که لاگین می‌کند این کیف پول را دریافت می‌کند
- اگر کیف پول مشترک موجود نباشد، به طور خودکار ایجاد می‌شود

#### کیف پول کاربری
- هر کاربر عادی کیف پول مخصوص خودش را دارد
- کاربران فقط می‌توانند کیف پول خود را شارژ کنند
- کاربران فقط تراکنش‌های خود را مشاهده می‌کنند

### 5. API Endpoints

#### قبل از توسعه
- هیچ endpoint عمومی برای wallet وجود نداشت

#### بعد از توسعه
```
GET /api/wallet                      - دریافت موجودی
GET /api/wallet/transactions          - دریافت تراکنش‌ها
POST /api/wallet/recharge             - شروع فرآیند شارژ
POST /api/wallet/callback             - کال‌بک از درگاه پرداخت
```

### 6. Integration با درگاه پرداخت

- ✅ یکپارچه‌سازی با ZarinpalService
- ✅ فرآیند کامل پرداخت از ایجاد درخواست تا تایید
- ✅ مدیریت callback از درگاه
- ✅ ثبت تراکنش‌های موفق و ناموفق
- ✅ به‌روزرسانی خودکار موجودی پس از پرداخت موفق

### 7. Validation و Security

- ✅ اعتبارسنجی مبلغ شارژ (حداقل ۱۰,۰۰۰ و حداکثر ۱,۰۰۰,۰۰۰ تومان)
- ✅ Policy برای کنترل دسترسی به کیف پول
- ✅ فقط ادمین‌ها می‌توانند از کیف پول مشترک استفاده کنند
- ✅ فقط کاربران می‌توانند از کیف پول خود استفاده کنند

### 8. UI/UX Features

- ✅ آیکن کیف پول در Header
- ✅ نمایش موجودی در فرمت خوانا
- ✅ نمایش آخرین تراکنش‌ها
- ✅ مودال شارژ با اعتبارسنجی
- ✅ نمایش اعداد به فارسی
- ✅ نمایش زیبا و حرفه‌ای با DaisyUI

### 9. تکنولوژی‌های استفاده شده

- **Backend**: Laravel 11, Repository Pattern, Service Pattern
- **Frontend**: React, TypeScript, DaisyUI, React Query
- **Payment Gateway**: Zarinpal (Sandbox/Production)
- **Testing**: Pest/PHPUnit

### 10. نحوه کار

1. **برای ادمین‌ها:**
   - کیف پول مشترک در زمان اجرای سیستم ایجاد می‌شود
   - همه ادمین‌ها به همان کیف پول دسترسی دارند
   - می‌توانند شارژ کنند و تراکنش‌ها را مشاهده کنند

2. **برای کاربران:**
   - کیف پول شخصی برای هر کاربر ایجاد می‌شود
   - فقط می‌توانند کیف پول خود را شارژ کنند
   - فقط تراکنش‌های خود را می‌بینند

3. **فرآیند شارژ:**
   - کاربر/ادمین مبلغ مورد نظر را وارد می‌کند
   - سیستم درخواست پرداخت به Zarinpal می‌زند
   - کاربر به درگاه پرداخت منتقل می‌شود
   - پس از پرداخت موفق، به سیستم بازمی‌گردد
   - موجودی به‌روزرسانی می‌شود و تراکنش ثبت می‌شود

### 11. Database Schema

#### جدول wallets
```sql
- id (bigint, primary key)
- user_id (bigint, nullable, foreign key)
- balance (bigint, default 0)
- currency (varchar(3), default 'IRR')
- is_shared (boolean, default false)  ← جدید
- created_at, updated_at
```

#### جدول wallet_transactions
```sql
- id (bigint, primary key)
- wallet_id (bigint, foreign key)
- type (enum: credit, debit)
- amount (bigint)
- balance_after (bigint)
- description (text)
- meta (json)
- created_at, updated_at
```

### 12. Status

این ویژگی **قبلاً پیاده‌سازی شده** بود و در این مرحله **به‌روزرسانی و توسعه یافت** تا شامل:
- کیف پول مشترک ادمین
- شارژ از طریق درگاه پرداخت
- UI/UX بهتر
- تست‌های جامع

### 13. Files Modified/Created

**Modified:**
- `app/Models/Wallet.php`
- `app/Repositories/WalletRepository.php`
- `app/Policies/WalletPolicy.php`
- `app/Providers/AppServiceProvider.php`
- `routes/api.php`
- `database/migrations/2025_10_25_000001_create_wallets_table.php`
- `client/src/hooks/useWallet.ts`
- `client/src/components/Header.tsx`

**Created:**
- `app/Services/WalletService.php`
- `app/Services/Interfaces/WalletServiceInterface.php`
- `app/Http/Controllers/Api/WalletController.php`
- `app/Http/Requests/WalletRechargeRequest.php`
- `database/migrations/2025_10_28_014423_add_is_shared_to_wallets_table.php`
- `database/migrations/2025_10_28_015350_modify_wallets_user_id_nullable.php`
- `database/seeders/SharedAdminWalletSeeder.php`
- `client/src/components/WalletDropdown.tsx`
- `web-api/tests/Feature/WalletTest.php`

### 14. Deployment Notes

برای راه‌اندازی:
```bash
# Run migrations
php artisan migrate

# Seed shared admin wallet
php artisan db:seed --class=SharedAdminWalletSeeder

# Run tests
php artisan test
```

### 15. Documentation

برای جزئیات بیشتر راجع به پیاده‌سازی، به مستندات زیر مراجعه کنید:
- `documents/wallet-implementation.md` - راهنمای کامل پیاده‌سازی
- `documents/api-readme.md` - API Documentation
- `web-api/tests/Feature/WalletTest.php` - Test Cases

