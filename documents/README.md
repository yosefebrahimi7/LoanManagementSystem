# Loan Management System

A production-ready Loan Management System built with Laravel that allows users to request loans, make payments through Zarinpal, and automatically calculates penalties for late payments.

## Features

- ✅ User authentication with Sanctum
- ✅ Loan request and approval system
- ✅ Automated payment schedule generation
- ✅ Penalty calculation for late payments
- ✅ Zarinpal payment gateway integration
- ✅ Personal wallet with transaction ledger
- ✅ Admin panel for loan management
- ✅ RESTful API for client applications

## Tech Stack

- **Laravel 12**
- **PHP 8.2+**
- **MySQL/PostgreSQL**
- **Sanctum** (API Authentication)
- **Pest** (Testing Framework)
- **TailwindCSS** (Frontend)

## Quick Start

1. Clone the repository
2. Install dependencies: `composer install && npm install`
3. Setup environment: `cp .env.example .env && php artisan key:generate`
4. Configure database in `.env`
5. Run migrations: `php artisan migrate --seed`
6. Start application: `php artisan serve && npm run dev`

## Documentation

- [Overview](readme.md) - نمای کلی
- [API Documentation](api-readme.md) - راهنمای کامل API
- [System Overview](system-overview.md) - نمای کلی سیستم
- [Features Summary](feature-summary.md) - خلاصه ویژگی‌ها
- [Installation Guide](installation.md) - نصب و راه‌اندازی
- [Configuration](configuration.md) - تنظیمات
- [Database Design](database-design.md) - طراحی دیتابیس
- [Development Guide](development.md) - راهنمای توسعه
- [Payment Integration](payment-integration.md) - سیستم پرداخت
- [Payment Test Guide](payment-test-guide.md) - راهنمای تست پرداخت
- [Environment Template](env.template) - نمونه تنظیمات

## License

MIT License
