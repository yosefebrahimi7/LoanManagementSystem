# راهنمای تست سیستم وام

## دستورات تست

### اجرای تست‌های عادی (پیشنهادی)
```bash
composer test
```
- تست‌ها را اجرا می‌کند
- **بعد از تست‌ها، دیتابیس را reset و seed می‌کند**
- برای استفاده روزمره توصیه می‌شود

### اجرای تست بدون Seed
```bash
composer test-no-seed
```
- فقط تست‌ها را اجرا می‌کند
- دیتابیس را seed نمی‌کند
- برای CI/CD مناسب است

### Seed کردن دستی دیتابیس
```bash
composer seed
```

### Reset و Seed کردن دیتابیس
```bash
composer fresh-seed
```
- همه جداول را حذف می‌کند
- Migrations را دوباره اجرا می‌کند
- Seeders را اجرا می‌کند

## ساختار تست‌ها

### Test Database
- تست‌ها از SQLite در حافظه (`:memory:`) استفاده می‌کنند
- سرعت بالا و عدم نیاز به دیتابیس واقعی
- هر test class یک دیتابیس تازه دریافت می‌کند

### Main Database
- بعد از اجرای تست‌ها، دیتابیس اصلی (MySQL) seed می‌شود
- این کار برای توسعه و تست دستی مفید است

## حساب‌های تست

بعد از اجرای seeders، حساب‌های زیر در دسترس هستند:

### Admin
- **Email:** `admin@example.com`
- **Password:** `password`
- **Role:** Admin

### Users
- **Email:** `test1@example.com`
- **Password:** `password`
- **Role:** User

- **Email:** `test2@example.com`
- **Password:** `password`
- **Role:** User

## Command های در دسترس

### `php artisan db:reset-and-seed`
Reset و Seed کردن دیتابیس

**Options:**
- `--force`: اجرا در محیط production
- `--fresh`: حذف کامل جداول و اجرای دوباره migrations

**مثال:**
```bash
php artisan db:reset-and-seed --fresh
```

## تنظیمات تست

### تغییر Database برای تست‌ها
برای استفاده از MySQL به جای SQLite در تست‌ها:

فایل `phpunit.xml` را ویرایش کنید:
```xml
<env name="DB_CONNECTION" value="mysql"/>
```
و کامنت را بردارید:
```xml
<!-- <env name="DB_CONNECTION" value="mysql"/> -->
```

## نکات مهم

1. ✅ **با `composer test` دیتابیس همیشه seed می‌شود** - برای دسترسی به حساب‌های تست
2. ✅ **تست‌ها از دیتابیس حافظه استفاده می‌کنند** - مستقل از دیتابیس اصلی هستند
3. ✅ **دیتابیس اصلی فقط seed می‌شود** - تست‌ها روی آن تأثیری نمی‌گذارند
4. ✅ **در CI/CD از `test-no-seed` استفاده کنید** - برای سرعت بیشتر

## حل مشکلات رایج

### خطای "Database seeded successfully!" نمایش نمی‌شود
- بررسی کنید که دیتابیس main قابل دسترسی است
- ممکن است از دیتابیس حافظه استفاده شده باشد (طبیعی است)

### خطای "Could not seed database"
- اطمینان حاصل کنید که `.env` به درستی تنظیم شده است
- بررسی کنید که دیتابیس main وجود دارد

### تست‌ها بسیار آهسته هستند
- بررسی کنید که از `DB_DATABASE=:memory:` استفاده می‌شود
- مطمئن شوید که تست‌ها از SQLite استفاده می‌کنند

