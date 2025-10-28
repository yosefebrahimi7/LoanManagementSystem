# خلاصه پیاده‌سازی سیستم کیف پول

## مقدمه
این سند شامل تمامی تغییرات انجام شده برای پیاده‌سازی سیستم کیف پول و ارتباط آن با وام‌ها و پرداخت‌ها است.

## ویژگی‌های پیاده‌سازی شده

### 1. تایید وام با محدودیت موجودی کیف پول ادمین
- **کنترل**: قبل از تایید هر وام، موجودی کیف پول مشترک ادمین‌ها چک می‌شود
- **پیام خطا**: در صورت کم بودن موجودی، پیام مناسب با جزئیات نمایش داده می‌شود
- **اعمال**: در فایل `LoanService.php` متد `processLoanApproval`

### 2. پرداخت اقساط از کیف پول کاربر
- **کنترل**: قبل از شروع پرداخت هر قسط، موجودی کیف پول کاربر چک می‌شود
- **پیام خطا**: در صورت کم بودن موجودی، پیام مناسب نمایش داده می‌شود
- **اعمال**: در فایل `PaymentService.php` متد `initiatePayment`

### 3. به‌روزرسانی خودکار کیف پول پس از پرداخت هر قسط
- **پس از پرداخت موفق**: مبلغ از کیف پول کاربر کسر و به کیف پول ادمین اضافه می‌شود
- **کسب و کار**: از طریق Background Job با نام `ProcessPaymentWalletUpdateJob`
- **Transaction**: تمام عملیات در یک Transaction انجام می‌شود تا از صحت داده‌ها اطمینان حاصل شود

### 4. به‌روزرسانی خودکار کیف پول کاربر پس از تایید وام
- **پس از تایید وام**: مبلغ وام به کیف پول کاربر اضافه می‌شود
- **کسب و کار**: از طریق Background Job با نام `DeductFromUserWalletJob`
- **Transaction**: تمام عملیات در یک Transaction انجام می‌شود

## استفاده از الگوهای طراحی مناسب

### Repository Pattern
- `WalletRepositoryInterface`: رابط مخزن کیف پول
- `WalletRepository`: پیاده‌سازی مخزن کیف پول
- متدهای جدید اضافه شده:
  - `deductBalance()`: کسر از موجودی با لاک
  - `hasSufficientBalance()`: بررسی کافی بودن موجودی

### Service Layer
- `LoanService`: مدیریت منطق وام‌ها با توجه به کیف پول
- `PaymentService`: مدیریت منطق پرداخت‌ها با توجه به کیف پول
- `NotificationService`: سرویس متمرکز برای ارسال نوتیفیکیشن

### Background Jobs
- `DeductFromUserWalletJob`: اضافه کردن مبلغ وام به کیف پول کاربر
- `ProcessPaymentWalletUpdateJob`: کسر از کیف پول کاربر و اضافه به کیف پول ادمین
- `SendNotificationJob`: ارسال نوتیفیکیشن‌های مختلف

### Request Forms & Policies
- `LoanApprovalRequest`: درخواست تایید/رد وام
- `PaymentRequest`: درخواست پرداخت قسط
- `WalletPolicy`: سیاست‌های دسترسی به کیف پول

## سیستم نوتیفیکیشن متمرکز

### انواع نوتیفیکیشن
1. **نوتیفیکیشن ناکافی بودن موجودی کاربر**
   - موقعی که موجودی کیف پول کاربر کمتر از مبلغ قسط است
   - شامل: موجودی فعلی و مبلغ مورد نیاز

2. **نوتیفیکیشن ناکافی بودن موجودی ادمین**
   - موقعی که موجودی کیف پول مشترک ادمین‌ها کمتر از مبلغ وام است
   - برای تمام ادمین‌ها ارسال می‌شود

3. **نوتیفیکیشن تایید/رد وام**
   - به کاربر اطلاع داده می‌شود

4. **نوتیفیکیشن موفقیت/عدم موفقیت پرداخت**
   - اطلاع‌رسانی به کاربر

### مدیریت نوتیفیکیشن
- `NotificationService`: سرویس متمرکز
- `SendNotificationJob`: Job متمرکز برای ارسال همه انواع نوتیفیکیشن‌ها
- تمام نوتیفیکیشن‌ها از طریق Background Job ارسال می‌شوند

## Transactions و Database Safety

### استفاده از Transactions
همه عملیات کیف پول در قالب Transaction انجام می‌شوند:

1. **WalletRepository.deductBalance()**
   - استفاده از `lockForUpdate()` برای جلوگیری از Race Condition
   - بررسی موجودی قبل از کسر
   - کسر اتمی

2. **DeductFromUserWalletJob**
   - Lock کردن کیف پول قبل از تغییر
   - ایجاد رکورد تراکنش در Transaction History

3. **ProcessPaymentWalletUpdateJob**
   - Lock کردن هر دو کیف پول (کاربر و ادمین)
   - بررسی موجودی قبل از کسر
   - کسر از کیف پول کاربر و اضافه به کیف پول ادمین در یک Transaction
   - ایجاد رکورد تراکنش برای هر دو کیف پول

4. **WalletService.completeRecharge()**
   - Lock کردن کیف پول
   - به‌روزرسانی موجودی و رکورد تراکنش در یک Transaction

### Rollback در صورت خطا
- در صورت هرگونه خطا، تمام عملیات انجام شده Rollback می‌شوند
- لاگ‌های مناسب برای Troubleshooting

## فایل‌های اصلاح شده

### Backend
1. `app/Repositories/WalletRepository.php`
   - اضافه کردن متد `deductBalance()`
   - اضافه کردن متد `hasSufficientBalance()`

2. `app/Repositories/Interfaces/WalletRepositoryInterface.php`
   - اضافه کردن interface متدهای جدید

3. `app/Services/LoanService.php`
   - اضافه کردن چک موجودی قبل از تایید وام
   - کسر از کیف پول ادمین با Transaction
   - ارسال نوتیفیکیشن در صورت ناکافی بودن موجودی

4. `app/Services/PaymentService.php`
   - اضافه کردن چک موجودی قبل از پرداخت
   - ارسال نوتیفیکیشن در صورت ناکافی بودن موجودی
   - Dispatch کردن Job برای به‌روزرسانی کیف پول‌ها

5. `app/Services/NotificationService.php` (جدید)
   - سرویس متمرکز برای مدیریت همه نوتیفیکیشن‌ها

6. `app/Services/WalletService.php`
   - اصلاح `completeRecharge()` برای استفاده از Transaction با Lock

### Jobs
1. `app/Jobs/DeductFromUserWalletJob.php` (جدید)
   - اضافه کردن مبلغ وام به کیف پول کاربر
   - استفاده از Transaction

2. `app/Jobs/ProcessPaymentWalletUpdateJob.php` (جدید)
   - کسر از کیف پول کاربر
   - اضافه به کیف پول ادمین
   - ایجاد رکورد تراکنش برای هر دو کیف پول
   - استفاده از Transaction با Lock

3. `app/Jobs/SendNotificationJob.php` (جدید)
   - Job متمرکز برای ارسال همه انواع نوتیفیکیشن‌ها

### Notifications
1. `app/Notifications/WalletInsufficientBalanceNotification.php` (جدید)
   - نوتیفیکیشن ناکافی بودن موجودی کیف پول

## مثال‌های استفاده

### تایید وام با کمبود موجودی
```php
// در LoanService.php
if (!$this->walletRepository->hasSufficientBalance($adminWallet->id, $loanAmountInRials)) {
    // ارسال نوتیفیکیشن به همه ادمین‌ها
    $this->notificationService->notifyAdminWalletLow(
        $adminWallet->balance,
        $loanAmountInRials
    );
    
    throw LoanException::badRequest('موجودی کیف پول مشترک ادمین ها کافی نیست...');
}
```

### پرداخت قسط با کمبود موجودی
```php
// در PaymentService.php
if (!$this->walletRepository->hasSufficientBalance($userWallet->id, $amountInRials)) {
    // ارسال نوتیفیکیشن به کاربر
    $this->notificationService->notifyWalletInsufficient(
        $user,
        $userWallet->balance,
        $amountInRials
    );
    
    throw LoanException::badRequest('موجودی کیف پول شما کافی نیست...');
}
```

## نکات مهم

1. **یکپارچگی داده‌ها**: همه عملیات کیف پول از Transaction استفاده می‌کنند
2. **Race Conditions**: استفاده از `lockForUpdate()` برای جلوگیری از مشکلات همزمانی
3. **نوتیفیکیشن‌ها**: همه از طریق Background Job ارسال می‌شوند
4. **لاگ‌گذاری**: تمام عملیات مهم لاگ می‌شوند
5. **بروزرسانی کش**: پس از هر تغییر، کش پاک می‌شود

## مراحل بعدی

1. تست‌های واحد برای تمام متدهای جدید
2. تست‌های یکپارچگی برای جریان کامل
3. افزودن Swagger documentation
4. به‌روزرسانی فرانت‌اند برای نمایش پیام‌های مناسب
5. اضافه کردن API endpoint برای نمایش موجودی کیف پول

