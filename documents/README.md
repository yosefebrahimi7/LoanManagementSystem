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

- [Installation Guide](installation.md)
- [API Documentation](api-documentation.md)
- [Database Design](database-design.md)
- [Configuration](configuration.md)
- [Development Guide](development.md)

## License

MIT License
