# راهنمای اجرای Background Jobs

## روش‌های اجرای Queue Worker

### 1️⃣ روش دستی (برای Development)

در یک terminal جداگانه، دستور زیر را اجرا کنید:

```bash
php artisan queue:work
```

یا برای اجرای مداوم (recommended):

```bash
php artisan queue:listen
```

**تفاوت بین `work` و `listen`:**
- `queue:work` - کارگر را یک بار اجرا می‌کند و متوقف می‌شود
- `queue:listen` - به طور مداوم گوش می‌دهد و jobs را اجرا می‌کند

### 2️⃣ روش اتوماتیک (Development با composer)

در پروژه شما یک script به نام `dev` تعریف شده که همه چیز را با هم اجرا می‌کند:

```bash
composer dev
```

این دستور هر سه قسمت زیر را به طور همزمان اجرا می‌کند:
- 🖥️ **Server**: `php artisan serve` (سرور Laravel)
- 📬 **Queue**: `php artisan queue:listen` (Background jobs)
- 🎨 **Vite**: `npm run dev` (Hot reload برای frontend)

### 3️⃣ برای Production (Windows Server)

در Windows، می‌توانید از Task Scheduler استفاده کنید یا یک batch file بسازید:

**گزینه 1: استفاده از NSSM (Non-Sucking Service Manager)**
```bash
# نصب NSSM
# دانلود از: https://nssm.cc/

# ایجاد سرویس
nssm install LaravelQueueWorker "C:\xampp\php\php.exe"
nssm set LaravelQueueWorker AppDirectory "C:\xampp\htdocs\LoanManagementSystem\web-api"
nssm set LaravelQueueWorker AppParameters "artisan queue:work --sleep=3 --tries=3"

# راه‌اندازی سرویس
nssm start LaravelQueueWorker
```

**گزینه 2: استفاده از Windows Task Scheduler**
1. Task Scheduler را باز کنید
2. Create Basic Task
3. در Action، این دستور را وارد کنید:
```bash
php C:\xampp\htdocs\LoanManagementSystem\web-api\artisan queue:work
```

**گزینه 3: استفاده از Supervisor (اگر از WSL استفاده می‌کنید)**

```bash
# ایجاد فایل config
sudo nano /etc/supervisor/conf.d/laravel-queue-worker.conf
```

محتوا:
```ini
[program:laravel-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/web-api/artisan queue:work
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/web-api/storage/logs/worker.log
```

### 4️⃣ اجرای در Background (Windows PowerShell)

برای اجرا در background در PowerShell:

```powershell
# روش 1: استفاده از Start-Job
Start-Job -ScriptBlock { php C:\xampp\htdocs\LoanManagementSystem\web-api\artisan queue:work }

# روش 2: اجرا در terminal جدید
Start-Process powershell -ArgumentList "-NoExit", "-Command", "php C:\xampp\htdocs\LoanManagementSystem\web-api\artisan queue:work"
```

## تنظیمات مهم

### بررسی وضعیت Jobs

```bash
# مشاهده لیست jobs در صف
php artisan queue:monitor

# پاک کردن failed jobs
php artisan queue:flush

# مشاهده failed jobs
php artisan queue:failed
```

### پاک کردن Queue Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan queue:restart
```

### تنظیم Environment Variables

در فایل `.env` خود، این تنظیمات را اضافه کنید:

```env
QUEUE_CONNECTION=database
DB_QUEUE_TABLE=jobs
DB_QUEUE=default
```

## Jobs موجود در پروژه

1. **SendWelcomeEmailJob** - ارسال ایمیل خوش‌آمدگویی هنگام ثبت‌نام
2. **SendLoanApprovalNotificationJob** - ارسال نوتیفیکیشن تأیید وام

## مدیریت Failed Jobs

```bash
# مشاهده failed jobs
php artisan queue:failed

# دوباره اجرا کردن failed job
php artisan queue:retry <job-id>

# حذف failed job
php artisan queue:forget <job-id>
```

## لاگ‌ها

لاگ‌های queue worker در مسیر زیر ذخیره می‌شوند:
```
web-api/storage/logs/laravel.log
```

## نکات مهم

⚠️ **اگر از `queue:work` استفاده می‌کنید:**
- پس از تغییر کد Job ها، باید worker را restart کنید
- از flag `--tries=3` برای تعداد تلاش‌های بیشتر استفاده کنید

✅ **توصیه برای Production:**
- همیشه از `queue:listen` استفاده کنید
- worker را با supervisor یا systemd مدیریت کنید
- هرگز دستور queue را در background اجرا نکنید (مگر با manager)

## مشکل‌یابی

**Problem**: Jobs اجرا نمی‌شوند
```bash
# راه حل 1: بررسی ارتباط با دیتابیس
php artisan tinker
>>> \DB::connection()->getPdo();

# راه حل 2: پاک کردن cache
php artisan config:clear
php artisan cache:clear

# راه حل 3: بررسی که worker در حال اجرا است
# در PowerShell
Get-Process | Where-Object {$_.ProcessName -like "*php*"}
```

**Problem**: Jobs خیلی دیر اجرا می‌شوند
- بررسی کنید که worker در حال اجرا است
- تعداد workers را افزایش دهید
- از Redis یا RabbitMQ استفاده کنید

## منابع

- [Laravel Queue Documentation](https://laravel.com/docs/queues)
- [Supervisor Configuration](http://supervisord.org/configuration.html)
- [Windows Service Creation](https://laravel.com/docs/queues#windows)

