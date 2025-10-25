# Installation Guide

## Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL/PostgreSQL
- Node.js & NPM

## Setup

1. Clone the repository:
```bash
git clone <repository-url>
cd LoanManagementSystem/web-api
```

2. Install dependencies:
```bash
composer install
npm install
```

3. Create environment file:
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure database in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=web_api
DB_USERNAME=root
DB_PASSWORD=
```

5. Run migrations and seeders:
```bash
php artisan migrate --seed
```

6. Run the application:
```bash
php artisan serve
npm run dev
```

## Seeded Accounts

### Admin Account
- **Email:** `admin@example.com`
- **Password:** `password`

### User Accounts
- **Email:** `user@example.com`
- **Password:** `password`
- **Email:** `jane@example.com`
- **Password:** `password`

## Running Tests

```bash
./vendor/bin/pest
```
