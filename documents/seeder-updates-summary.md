# خلاصه اصلاحات Seeder ها

## تغییرات انجام شده

### 1. UserSeeder
**فایل:** `database/seeders/UserSeeder.php`

#### تغییرات:
- اضافه شدن `currency` و `is_shared` به کیف پول‌ها
- مشخص شدن واحد پول: Rials (IRR)
- `balance` در Rials ذخیره می‌شود

```php
Wallet::updateOrCreate(
    ['user_id' => $user->id],
    [
        'balance' => 0, // in Rials
        'currency' => 'IRR',
        'is_shared' => false,
    ]
);
```

### 2. SharedAdminWalletSeeder
**فایل:** `database/seeders/SharedAdminWalletSeeder.php`

#### تغییرات:
- کیف پول ادمین مشترک با موجودی اولیه ایجاد می‌شود
- موجودی: **100,000,000 Rials = 10,000,000 Tomans**
- استفاده از `updateOrCreate` برای جلوگیری از ایجاد تکراری

```php
$existingWallet = Wallet::updateOrCreate(
    [
        'is_shared' => true,
        'user_id' => null,
    ],
    [
        'balance' => 100000000, // 100M Rials = 10M Tomans
        'currency' => 'IRR',
    ]
);
```

### 3. LoanSeeder
**فایل:** `database/seeders/LoanSeeder.php`

#### تغییرات:
- اضافه شدن `is_shared` به کیف پول‌های کاربران
- موجودی تصادفی: 0 تا 5M Rials (0 تا 500K Tomans)
- واحد پول: Rials

```php
Wallet::firstOrCreate(
    ['user_id' => $user->id],
    [
        'user_id' => $user->id,
        'balance' => rand(0, 5000000), // 0 to 5M Rials
        'currency' => 'IRR',
        'is_shared' => false,
    ]
);
```

### 4. WalletFactory
**فایل:** `database/factories/WalletFactory.php`

#### تغییرات:
- اضافه شدن `is_shared => false` به definition
- اضافه شدن متد `sharedAdmin()` برای ایجاد کیف پول ادمین مشترک
- به‌روزرسانی کامنت‌ها برای واحدهای صحیح

```php
public function sharedAdmin(): static
{
    return $this->state(fn (array $attributes) => [
        'user_id' => null,
        'balance' => 100000000, // 100M Rials = 10M Tomans
        'is_shared' => true,
    ]);
}
```

## واحدهای پول

### Rials (IRR)
- واحد اصلی ذخیره‌سازی در دیتابیس
- کیف پول‌ها: balance در Rials
- مثال: 10,000,000 Rials = 1,000,000 Tomans

### Tomans
- واحد نمایش برای کاربر
- در دیتابیس ذخیره نمی‌شود
- فقط برای نمایش استفاده می‌شود
- فرمول: `Toman = Rial / 10`

## موجودی های پیش‌فرض

### کیف پول ادمین مشترک
- **موجودی:** 100,000,000 Rials (10M Tomans)
- **هدف:** سرمایه اولیه برای تایید وام‌ها

### کیف پول کاربران
- **موجودی:** 0 Rials (0 Tomans)
- **زمان seed:** اعطای موجودی تصادفی

### کیف پول کاربران (با LoanSeeder)
- **موجودی:** 0 تا 5,000,000 Rials (0 تا 500K Tomans)
- **هدف:** تست و دمو

## خروجی Seeder

```
Database\Seeders\UserSeeder ........................................ RUNNING  
Database\Seeders\UserSeeder .................................. 1,261 ms DONE  

Database\Seeders\SettingSeeder ..................................... RUNNING  
Database\Seeders\SettingSeeder .................................. 35 ms DONE  

Database\Seeders\SharedAdminWalletSeeder ........................... RUNNING  
Shared admin wallet created with initial balance of 10,000,000 Tomans.
Database\Seeders\SharedAdminWalletSeeder ........................ 43 ms DONE  

Database\Seeders\LoanSeeder ........................................ RUNNING  
Database\Seeders\LoanSeeder ..................................... 42 ms DONE  
```

## تست‌ها
- همه 49 تست passing (199 assertions)
- تست‌ها با واحدهای جدید سازگار هستند

## دستورات مفید

```bash
# Seed کردن دیتابیس
php artisan db:seed

# Reset و Seed مجدد
php artisan migrate:fresh --seed

# تست کردن
php artisan test

# تست + Seed خودکار
composer test
```

## نکات مهم

1. ✅ همه موجودی‌ها در Rials ذخیره می‌شوند
2. ✅ کیف پول ادمین با سرمایه اولیه ایجاد می‌شود
3. ✅ `is_shared` flag برای تشخیص کیف پول مشترک
4. ✅ تست‌ها با واحدهای جدید سازگار هستند

