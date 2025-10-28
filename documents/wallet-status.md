# وضعیت کیف پول - Wallet Status

## ✅ تکمیل شده (Completed)

### Backend
- ✅ Model Wallet با فیلد is_shared
- ✅ Repository Pattern با متدهای getSharedAdminWallet و getOrCreateSharedAdminWallet
- ✅ Service Layer با WalletService
- ✅ Controller با 4 endpoint کامل
- ✅ Form Request برای اعتبارسنجی
- ✅ Policy برای کنترل دسترسی
- ✅ Migration برای افزودن is_shared و nullable کردن user_id
- ✅ Seeder برای ایجاد کیف پول مشترک ادمین
- ✅ ثبت سرویس و Policy در AppServiceProvider
- ✅ Routes با ساختار مناسب
- ✅ Swagger Documentation کامل
- ✅ تست‌های جامع (20+ تست)

### Frontend
- ✅ WalletDropdown component با UI زیبا
- ✅ UserDropdown component (جداگانه)
- ✅ Header با آیکن کیف پول
- ✅ Pages برای success و failed
- ✅ Hooks برای API calls
- ✅ Routes اضافه شده
- ✅ Format amount با اعداد فارسی
- ✅ نمایش مبلغ به حروف فارسی
- ✅ حداقل و حداکثر شارژ

### خصوصیات کیف پول

#### کیف پول مشترک ادمین
- ایجاد خودکار هنگام seed
- user_id = null
- is_shared = true
- فقط ادمین‌ها به آن دسترسی دارند
- تمام ادمین‌ها یک کیف پول مشترک دارند

#### کیف پول کاربری
- ایجاد خودکار هنگام ثبت‌نام
- user_id = user_id کاربر
- is_shared = false
- هر کاربر کیف پول مخصوص خود را دارد

### فرآیند شارژ

1. کاربر مبلغ را وارد می‌کند (10,000 تا 2,000,000,000 تومان)
2. اعتبارسنجی در Frontend و Backend
3. تبدیل تومان به ریال (× 10)
4. ایجاد تراکنش pending
5. ارسال به درگاه زرین‌پال
6. پرداخت کاربر
7. Callback به سرور
8. تایید پرداخت از زرین‌پال
9. بروزرسانی موجودی کیف پول
10. ثبت تراکنش با وضعیت success
11. هدایت به صفحه موفقیت/خطا

### محدودیت‌ها

#### مبلغ شارژ
- حداقل: 10,000 تومان (100,000 ریال)
- حداکثر: 2,000,000,000 تومان (20,000,000,000 ریال)

#### دسترسی
- کاربران: فقط کیف پول خود
- ادمین‌ها: فقط کیف پول مشترک

### واحدهای پولی

#### Database
- موجودی: ریال (Rials)
- تراکنش‌ها: ریال (Rials)

#### API
- ورودی: تومان (Tomans)
- خروجی: تومان (Tomans)
- نمایش: تومان (Tomans)

#### Zarinpal
- پرداخت: ریال (Rials)
- تایید: ریال (Rials)

#### تبدیل واحدها
```
1 Toman = 10 Rials
Example: 1,000,000 تومان = 10,000,000 ریال
```

### API Endpoints

#### GET /api/wallet
دریافت موجودی کیف پول

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

#### GET /api/wallet/transactions
دریافت تراکنش‌های کیف پول

**Query Parameters:**
- page (integer)
- limit (integer)

**Response:**
```json
{
  "success": true,
  "data": [...],
  "meta": {...}
}
```

#### POST /api/wallet/recharge
شروع شارژ کیف پول

**Request:**
```json
{
  "amount": 1000000,
  "method": "zarinpal"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "payment_url": "https://sandbox.zarinpal.com/...",
    "authority": "A00000000000000000000000000000000000",
    "transaction_id": 1
  }
}
```

#### POST /api/wallet/callback
پردازش بازگشت از درگاه (Public)

**Parameters:**
- Authority
- Status

**Response:**
Redirect to frontend

## 📊 آمار

- **فایل‌های Backend ایجاد شده:** 15+
- **فایل‌های Frontend ایجاد شده:** 8+
- **تست‌ها:** 20+
- **API Endpoints:** 4
- **Components:** 3
- **مستندات:** 5 فایل

## 🎯 Acceptance Criteria

✅ کیف پول مشترک برای ادمین‌ها  
✅ کیف پول شخصی برای هر کاربر  
✅ شارژ از طریق زرین‌پال  
✅ UI/UX زیبا و کاربردی  
✅ Repository Pattern  
✅ Policies برای دسترسی  
✅ Form Requests برای اعتبارسنجی  
✅ Services برای منطق تجاری  
✅ تست‌های جامع  
✅ Swagger Documentation  

## 🔄 Changelog

### Version 1.0.0 (2024-01-01)
- ✅ پیاده‌سازی کامل کیف پول
- ✅ کیف پول مشترک ادمین
- ✅ کیف پول کاربری
- ✅ شارژ از زرین‌پال
- ✅ UI/UX کامل
- ✅ تست‌های جامع
- ✅ مستندات Swagger

## 📝 Notes

- کیف پول مشترک ادمین در زمان seed ایجاد می‌شود
- کیف پول کاربری هنگام اولین استفاده ایجاد می‌شود
- تمام عملیات به صورت Atomic انجام می‌شود
- Cache برای بهبود عملکرد استفاده شده است
- مستندات در `documents/wallet-implementation.md` موجود است
