# مستندات سیستم پرداخت زرین‌پال

## 📋 فهرست مطالب
1. [مقدمه](#مقدمه)
2. [معماری سیستم](#معماری-سیستم)
3. [راه‌اندازی](#راه-اندازی)
4. [جریان پرداخت](#جریان-پرداخت)
5. [API Endpoints](#api-endpoints)
6. [تست Sandbox](#تست-sandbox)
7. [مدیریت خطاها](#مدیریت-خطاها)

---

## 🎯 مقدمه

این سیستم یکپارچه‌سازی با درگاه پرداخت زرین‌پال برای سیستم مدیریت وام است که امکان پرداخت اقساط وام را فراهم می‌کند.

### ویژگی‌ها
- ✅ پرداخت اقساط وام
- ✅ استفاده از Sandbox برای تست
- ✅ مدیریت وضعیت‌های مختلف پرداخت
- ✅ به‌روزرسانی خودکار باقی‌مانده وام
- ✅ تاریخچه کامل پرداخت‌ها

---

## 🏗️ معماری سیستم

### Backend (Laravel)

#### 1. سرویس‌ها
- **ZarinpalService** (`app/Services/ZarinpalService.php`)
  - ارتباط با API زرین‌پال
  - درخواست پرداخت
  - تایید پرداخت

#### 2. کنترلرها
- **PaymentController** (`app/Http/Controllers/Api/PaymentController.php`)
  - `initiatePayment()`: شروع پرداخت
  - `callback()`: دریافت بازگشت از درگاه
  - `paymentStatus()`: وضعیت پرداخت
  - `paymentHistory()`: تاریخچه پرداخت‌ها

#### 3. مدل‌ها
- **LoanPayment**: اطلاعات پرداخت
- **LoanSchedule**: برنامه پرداخت اقساط
- **Loan**: اطلاعات وام

### Frontend (React + TypeScript)

#### صفحات
- **LoanPayment.tsx**: صفحه پرداخت اقساط
- **PaymentSuccess.tsx**: صفحه موفقیت پرداخت
- **PaymentFailed.tsx**: صفحه شکست پرداخت

#### Hookها
- **usePayments.ts**: مدیریت API پرداخت‌ها

---

## 🔧 راه‌اندازی

### تنظیمات Backend

#### 1. نصب پکیج‌ها
```bash
cd web-api
composer require zarinpal/zarinpal-php-sdk
```

#### 2. تنظیمات `.env`
```env
# Zarinpal Configuration
ZARINPAL_MERCHANT_ID=00000000-0000-0000-0000-000000000000
ZARINPAL_SANDBOX=true
ZARINPAL_CALLBACK_URL=http://localhost:8000/api/payment/callback
FRONTEND_URL=http://localhost:5174
```

#### 3. اجرای Migrations
```bash
php artisan migrate
```

### تنظیمات Frontend

#### Route‌ها
موارد زیر در `App.tsx` اضافه شده‌اند:
- `/loan-payment/:id`: صفحه پرداخت اقساط
- `/payment/success`: صفحه موفقیت پرداخت
- `/payment/failed`: صفحه شکست پرداخت

---

## 🔄 جریان پرداخت

### 1. شروع پرداخت

**Frontend:**
```typescript
// کاربر روی دکمه "پرداخت" کلیک می‌کند
const handlePayment = (scheduleId: number) => {
  initiatePayment.mutate({ 
    loanId: loan.id, 
    scheduleId 
  });
};
```

**Backend:**
```php
// POST /api/payment/loans/{loan}/initiate
POST body: {
  "schedule_id": 1,
  "amount": 500000  // اختیاری، اگر نباشد مبلغ باقی‌مانده استفاده می‌شود
}
```

### 2. اتصال به درگاه

بعد از درخواست موفق، کاربر به درگاه زرین‌پال redirect می‌شود:
```
https://sandbox.zarinpal.com/pg/StartPay/{authority}
```

### 3. پرداخت در Sandbox

**اطلاعات کارت تست:**
- شماره کارت: `6037991234567890`
- CVV2: هر عدد ۳ رقمی
- تاریخ انقضاء: هر تاریخ آینده
- کلمه عبور: هر عدد ۴ رقمی

### 4. بازگشت از درگاه

بعد از پرداخت، زرین‌پال به URL زیر redirect می‌کند:
```
GET http://localhost:8000/api/payment/callback?Authority=...&Status=OK
```

### 5. تایید پرداخت

Backend موارد زیر را انجام می‌دهد:
1. پرداخت را با زرین‌پال verify می‌کند
2. وضعیت پرداخت را به `completed` تغییر می‌دهد
3. `paid_amount` در schedule به‌روزرسانی می‌شود
4. وضعیت schedule را بررسی می‌کند (`paid`, `partial`, `pending`)
5. `remaining_balance` وام به‌روزرسانی می‌شود
6. اگر تمام وام پرداخت شده، وضعیت وام را به `paid` تغییر می‌دهد

### 6. Redirect به Frontend

بعد از پردازش، کاربر به یکی از صفحات زیر redirect می‌شود:

**موفق:**
```
http://localhost:5174/payment/success?payment_id=7
```

**خطا:**
```
http://localhost:5174/payment/failed?payment_id=7&error=...
```

---

## 📡 API Endpoints

### 1. شروع پرداخت
```
POST /api/payment/loans/{loan}/initiate
Authorization: Bearer {token}

Body:
{
  "schedule_id": 1,
  "amount": 500000  // اختیاری
}

Response:
{
  "success": true,
  "payment_url": "https://sandbox.zarinpal.com/...",
  "authority": "S000...",
  "payment_id": 7
}
```

### 2. Callback از درگاه
```
GET|POST /api/payment/callback?Authority=...&Status=OK
Redirect: /payment/success?payment_id={id}
```

### 3. وضعیت پرداخت
```
GET /api/payment/status/{paymentId}
Authorization: Bearer {token}

Response:
{
  "success": true,
  "payment": {
    "id": 7,
    "status": "completed",
    "amount": 500000,
    "created_at": "2025-10-26T..."
  }
}
```

### 4. تاریخچه پرداخت‌ها
```
GET /api/payment/history
Authorization: Bearer {token}

Response:
{
  "success": true,
  "payments": [...]
}
```

---

## 🧪 تست Sandbox

### مراحل تست

1. **یک وام ایجاد کنید** (با وضعیت `approved`)
2. **به صفحه پرداخت بروید**: `/loan-payment/{loan_id}`
3. **روی دکمه "پرداخت" کلیک کنید**
4. **به درگاه منتقل می‌شوید**
5. **با کارت تست پرداخت کنید**:
   - Card: `6037991234567890`
   - CVV2: `123`
   - Expiry: `12/25`
   - Password: `1234`
6. **پس از پرداخت موفق، به صفحه موفقیت منتقل می‌شوید**

### بررسی نتیجه

1. **برنامه پرداخت**: وضعیت قسط به `paid` تغییر می‌کند
2. **باقیمانده وام**: به‌روزرسانی می‌شود
3. **تاریخچه**: پرداخت در تاریخچه ثبت می‌شود

---

## ⚠️ مدیریت خطاها

### کدهای خطا

| کد | معنا | راهکار |
|----|------|--------|
| 100 | موفق | - |
| 101 | پرداخت قبلاً تایید شده | به عنوان موفق در نظر گرفته می‌شود |
| -9 | پرداخت نامعتبر | بررسی authority و amount |
| -11 | Merchant ID نامعتبر | بررسی تنظیمات |
| -21 | Authority نامعتبر | - |

### لاگ‌ها

همه مراحل در `storage/logs/laravel.log` ثبت می‌شوند:

```
[2025-10-26 14:29:24] local.INFO: Payment callback received {"authority":"...","status":"OK"}
[2025-10-26 14:29:25] local.INFO: Verifying payment {"payment_id":7,"has_schedule":true,"has_loan":true}
[2025-10-26 14:29:26] local.INFO: Schedule updated {"schedule_id":1,"paid_amount":505850,"status":"paid"}
[2025-10-26 14:29:26] local.INFO: Loan updated {"loan_id":15,"remaining_balance":0,"status":"paid"}
```

---

## 🔐 امنیت

### Best Practices

1. **HTTPS**: در production حتماً از HTTPS استفاده کنید
2. **Authorization**: همه endpoint‌ها نیاز به authentication دارند
3. **Validation**: همه ورودی‌ها validate می‌شوند
4. **Transaction**: از Transaction استفاده می‌شود برای ensure consistency
5. **Logging**: همه عملیات لاگ می‌شوند

### Merchant ID

- **Sandbox**: `00000000-0000-0000-0000-000000000000`
- **Production**: باید از پنل زرین‌پال دریافت کنید

---

## 📊 فلوچارت پرداخت

```
┌─────────┐
│ کاربر   │
└────┬────┘
     │ روی "پرداخت" کلیک می‌کند
     ▼
┌─────────────────────────┐
│ initiatePayment API     │
│ - Payment record ایجاد  │
│ - درخواست به زرین‌پال   │
└────┬────────────────────┘
     │
     ▼ redirect
┌─────────────────────────┐
│ درگاه زرین‌پال (Sandbox)│
└────┬────────────────────┘
     │ کاربر پرداخت می‌کند
     ▼
┌─────────────────────────┐
│ Callback API            │
│ - Verify payment         │
│ - Update schedule        │
│ - Update loan            │
└────┬────────────────────┘
     │
     ▼ redirect
┌─────────────────────────┐
│ Frontend - Success Page │
└─────────────────────────┘
```

---

## 🎨 Frontend Components

### LoanPayment.tsx
صفحه اصلی پرداخت که شامل:
- لیست اقساط قابل پرداخت
- اطلاعات وام
- دکمه‌های پرداخت برای هر قسط

### PaymentSuccess.tsx
نمایش اطلاعات پرداخت موفق:
- شناسه پرداخت
- مبلغ پرداخت شده
- دکمه بازگشت

### PaymentFailed.tsx
نمایش خطا در صورت شکست:
- پیام خطا
- دکمه تلاش مجدد

---

## 🐛 عیب‌یابی

### مشکل: Redirect به پورت اشتباه

**راه‌حل:**
```bash
# .env را بررسی کنید
FRONTEND_URL=http://localhost:5174

# Cache را پاک کنید
php artisan config:clear
php artisan cache:clear

# سرور را restart کنید
```

### مشکل: وضعیت به‌روزرسانی نمی‌شود

**راه‌حل:**
1. لاگ‌ها را بررسی کنید
2. مطمئن شوید schedule و loan موجودند
3. Transaction انجام شده باشد

### مشکل: کد 101

این یعنی پرداخت قبلاً تایید شده. سیستم این را تشخیص می‌دهد و به‌روزرسانی می‌کند.

---

## 📝 کدهای مهم

### ZarinpalService

```php
public function requestPayment(int $amount, string $description, ...): array
{
    // درخواست پرداخت به زرین‌پال
}

public function verifyPayment(string $authority, int $amount): array
{
    // تایید پرداخت
}
```

### PaymentController

```php
public function initiatePayment(Request $request, Loan $loan): JsonResponse
{
    // ایجاد رکورد پرداخت و redirect به درگاه
}

public function callback(Request $request)
{
    // دریافت callback و پردازش
}
```

---

## 🚀 آماده برای Production

برای استفاده در Production:

1. `ZARINPAL_SANDBOX=false` کنید
2. `ZARINPAL_MERCHANT_ID` واقعی را تنظیم کنید
3. HTTPS را فعال کنید
4. دامنه production را در callback URL قرار دهید

---

## 📞 پشتیبانی

در صورت هرگونه مشکل یا سوال:
1. لاگ‌ها را بررسی کنید: `storage/logs/laravel.log`
2. مرجع زرین‌پال: https://doc.zarinpal.com
3. تست Sandbox: همیشه قبل از production تست کنید

---

**آخرین بروزرسانی**: 26 اکتبر 2025

