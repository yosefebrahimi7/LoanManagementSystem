# راه‌حل Seed کردن خودکار دیتابیس بعد از تست‌ها

## مشکل
بعد از اجرای تست‌ها، دیتابیس خالی می‌شد و باید به صورت دستی دوباره seed می‌شد.

## راه‌حل ساده و کاربردی

### Composer Scripts ساده

چندین script جدید در `composer.json` اضافه شده:

#### اجرای تست‌ها و seed خودکار (پیشنهادی)
```bash
composer test
```
- تست‌ها را اجرا می‌کند
- **بعد از تست‌ها دیتابیس را reset و seed می‌کند**
- دیتابیس همیشه پر از داده‌های تست است

#### اجرای تست بدون seed
```bash
composer test-no-seed
```
- فقط تست‌ها را اجرا می‌کند
- دیتابیس seed نمی‌شود

#### Seed کردن دستی
```bash
composer seed
```

#### Reset و Seed
```bash
composer fresh-seed
```

## نحوه کار

### Flow اجرای تست‌ها

```
1. composer test
   ↓
2. Clear config cache
   ↓
3. Run PHPUnit tests (SQLite in-memory)
   ↓
4. php artisan migrate:fresh --seed --force
   ↓
5. Database reset and seeded! ✅
```

### تست‌ها از چه دیتابیسی استفاده می‌کنند؟

**تست‌ها:**
- از SQLite در حافظه (`:memory:`) استفاده می‌کنند
- سریع هستند
- مستقل از دیتابیس اصلی هستند

**دیتابیس اصلی (MySQL):**
- بعد از تست‌ها seed می‌شود
- برای تست دستی و توسعه استفاده می‌شود
- تست‌ها روی آن تأثیری نمی‌گذارند

## استفاده

### برای توسعه روزمره
```bash
composer test
```
این دستور بعد از تست‌ها دیتابیس را به طور خودکار seed می‌کند.

### برای CI/CD
```bash
composer test-no-seed
```
برای جلوگیری از seed شدن دیتابیس در CI/CD.

### برای دسترسی سریع به حساب‌های تست
```bash
composer fresh-seed
```
برای reset و seed کردن سریع دیتابیس.

## حساب‌های تست

بعد از seed شدن، این حساب‌ها در دسترس هستند:

### Admin
- Email: `admin@example.com`
- Password: `password`

### Users
- Email: `test1@example.com`
- Password: `password`
- Email: `test2@example.com`
- Password: `password`

## نکات مهم

1. ✅ **تست‌ها از دیتابیس حافظه استفاده می‌کنند** - مستقل از دیتابیس اصلی
2. ✅ **دیتابیس اصلی فقط seed می‌شود** - تست‌ها آن را نمی‌تکونند
3. ✅ **در production اجرا نمی‌شود** - امنیت
4. ✅ **اگر دیتابیس حافظه باشد seed نمی‌شود** - طبیعی است

## عیب‌یابی

### Seed نمی‌شود
- بررسی کنید که `.env` به درستی تنظیم شده
- مطمئن شوید که از دیتابیس حافظه استفاده نمی‌کنید

### خطای Database Connection
- مطمئن شوید MySQL در حال اجرا است
- بررسی کنید که دیتابیس در `.env` وجود دارد

### Seed می‌شود اما حساب‌ها در دسترس نیستند
- چک کنید که seeder ها به درستی اجرا شده‌اند
- از `composer fresh-seed` استفاده کنید

## فایل‌های مربوطه

- `tests/TestCase.php` - TestCase ساده (بدون تغییر از Laravel استاندارد)
- `tests/CreatesApplication.php` - Trait ایجاد اپلیکیشن
- `composer.json` - Scripts بهبود یافته
- `documents/testing-guide.md` - راهنمای کامل تست

