# Loan Management System API Documentation

## Overview
This API provides endpoints for managing loans, including creating loan requests, approving/rejecting loans, making payments, and managing user wallets.

## Base URL
```
http://localhost:8000/api
```

## Authentication
All protected endpoints require a Bearer token obtained from the authentication endpoints.

### Headers
```
Authorization: Bearer {your_token}
Content-Type: application/json
Accept: application/json
```

## Endpoints

### Authentication

#### Register User
```http
POST /auth/register
```

**Request Body:**
```json
{
    "firstName": "John",
    "lastName": "Doe",
    "email": "john@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "message": "User registered successfully",
    "data": {
        "user": {
            "id": 1,
            "first_name": "John",
            "last_name": "Doe",
            "email": "john@example.com",
            "is_active": true
        },
        "token": "1|abc123...",
        "refreshToken": "1|abc123..."
    }
}
```

#### Login User
```http
POST /auth/login
```

**Request Body:**
```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "first_name": "John",
            "last_name": "Doe",
            "email": "john@example.com",
            "is_active": true
        },
        "token": "1|abc123...",
        "refreshToken": "1|abc123..."
    }
}
```

#### Logout User
```http
POST /auth/logout
```

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

#### Get Current User
```http
GET /auth/me
```

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "first_name": "John",
        "last_name": "Doe",
        "email": "john@example.com",
        "is_active": true
    }
}
```

### Loan Management

#### Get User's Loans
```http
GET /loans
```

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "amount": 10000000,
            "term_months": 12,
            "interest_rate": 14.5,
            "monthly_payment": 900000,
            "remaining_balance": 10000000,
            "status": "pending",
            "start_date": "2024-01-01",
            "created_at": "2024-01-01T00:00:00.000000Z",
            "updated_at": "2024-01-01T00:00:00.000000Z",
            "schedules": [],
            "payments": []
        }
    ]
}
```

#### Create Loan Request
```http
POST /loans
```

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
    "amount": 10000000,
    "term_months": 12,
    "interest_rate": 14.5,
    "start_date": "2024-01-01"
}
```

**Validation Rules:**
- `amount`: required|integer|min:1000000|max:100000000 (1M to 100M IRR)
- `term_months`: required|integer|min:3|max:36
- `interest_rate`: nullable|numeric|min:0|max:50
- `start_date`: required|date|after_or_equal:today

**Response:**
```json
{
    "success": true,
    "message": "Loan request submitted successfully",
    "data": {
        "id": 1,
        "user_id": 1,
        "amount": 10000000,
        "term_months": 12,
        "interest_rate": 14.5,
        "monthly_payment": 900000,
        "remaining_balance": 10000000,
        "status": "pending",
        "start_date": "2024-01-01",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

#### Get Specific Loan
```http
GET /loans/{id}
```

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "user_id": 1,
        "amount": 10000000,
        "term_months": 12,
        "interest_rate": 14.5,
        "monthly_payment": 900000,
        "remaining_balance": 10000000,
        "status": "approved",
        "start_date": "2024-01-01",
        "approved_at": "2024-01-02T00:00:00.000000Z",
        "approved_by": 2,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-02T00:00:00.000000Z",
        "schedules": [
            {
                "id": 1,
                "loan_id": 1,
                "installment_number": 1,
                "amount_due": 900000,
                "principal_amount": 800000,
                "interest_amount": 100000,
                "due_date": "2024-02-01",
                "status": "pending"
            }
        ],
        "payments": [],
        "user": {
            "id": 1,
            "first_name": "John",
            "last_name": "Doe",
            "email": "john@example.com"
        },
        "approvedBy": {
            "id": 2,
            "first_name": "Admin",
            "last_name": "User",
            "email": "admin@loanmanagement.com"
        }
    }
}
```

### Admin Endpoints

#### Get All Loans (Admin)
```http
GET /admin/loans
```

**Headers:** `Authorization: Bearer {admin_token}`

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "amount": 10000000,
            "term_months": 12,
            "interest_rate": 14.5,
            "monthly_payment": 900000,
            "remaining_balance": 10000000,
            "status": "pending",
            "start_date": "2024-01-01",
            "created_at": "2024-01-01T00:00:00.000000Z",
            "updated_at": "2024-01-01T00:00:00.000000Z",
            "user": {
                "id": 1,
                "first_name": "John",
                "last_name": "Doe",
                "email": "john@example.com"
            },
            "schedules": [],
            "payments": [],
            "approvedBy": null
        }
    ]
}
```

#### Approve/Reject Loan (Admin)
```http
POST /admin/loans/{id}/approve
```

**Headers:** `Authorization: Bearer {admin_token}`

**Request Body (Approve):**
```json
{
    "action": "approve"
}
```

**Request Body (Reject):**
```json
{
    "action": "reject",
    "rejection_reason": "Insufficient credit history"
}
```

**Response (Approve):**
```json
{
    "success": true,
    "message": "Loan approved successfully",
    "data": {
        "id": 1,
        "user_id": 1,
        "amount": 10000000,
        "term_months": 12,
        "interest_rate": 14.5,
        "monthly_payment": 900000,
        "remaining_balance": 10000000,
        "status": "approved",
        "start_date": "2024-01-01",
        "approved_at": "2024-01-02T00:00:00.000000Z",
        "approved_by": 2,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-02T00:00:00.000000Z",
        "schedules": [
            {
                "id": 1,
                "loan_id": 1,
                "installment_number": 1,
                "amount_due": 900000,
                "principal_amount": 800000,
                "interest_amount": 100000,
                "due_date": "2024-02-01",
                "status": "pending"
            }
        ]
    }
}
```

#### Get Loan Statistics (Admin)
```http
GET /admin/loans/stats
```

**Headers:** `Authorization: Bearer {admin_token}`

**Response:**
```json
{
    "success": true,
    "data": {
        "total_loans": 15,
        "pending_loans": 5,
        "approved_loans": 3,
        "rejected_loans": 2,
        "active_loans": 2,
        "delinquent_loans": 1,
        "paid_loans": 1,
        "total_amount": 50000000,
        "formatted_total_amount": "500,000.00",
        "monthly_loans": 8,
        "monthly_amount": 25000000,
        "formatted_monthly_amount": "250,000.00"
    },
    "meta": {
        "generated_at": "2024-01-15T10:30:00.000000Z",
        "currency": "IRR"
    }
}
```

## Error Responses

### Validation Error (422)
```json
{
    "success": false,
    "message": "اطلاعات وارد شده نامعتبر است",
    "errors": {
        "amount": ["Minimum loan amount is 1,000,000 IRR"],
        "term_months": ["Minimum loan term is 3 months"]
    }
}
```

### Unauthorized (401)
```json
{
    "success": false,
    "message": "دسترسی غیرمجاز"
}
```

### Forbidden (403)
```json
{
    "success": false,
    "message": "شما به این بخش دسترسی ندارید"
}
```

### Not Found (404)
```json
{
    "success": false,
    "message": "اطلاعات مورد نظر یافت نشد"
}
```

### Server Error (500)
```json
{
    "success": false,
    "message": "خطا در انجام عملیات"
}
```

## Common Issues & Solutions

### Statistics API Issues

#### Issue: "Undefined array key" errors
**Problem**: Missing keys in statistics array
**Solution**: Use the `LoanStatisticsService` which provides all required keys with safe fallback values

#### Issue: Statistics not updating
**Problem**: Cached statistics showing old data
**Solution**: Clear cache using `LoanStatisticsService::clearCache()` or wait 5 minutes for automatic refresh

### API Resource Issues

#### Issue: Missing formatted values
**Problem**: Raw integer values without formatting
**Solution**: Ensure using API Resources instead of raw model data

#### Issue: Missing relationships
**Problem**: Related data not included in response
**Solution**: Use `with()` method to eager load relationships before passing to resources

### Performance Issues

#### Issue: Slow statistics queries
**Problem**: Multiple database queries for statistics
**Solution**: Statistics are cached for 5 minutes to improve performance

#### Issue: Large response sizes
**Problem**: Too much data in API responses
**Solution**: Use conditional relationships (`whenLoaded()`) to include only needed data

## Data Types

### Monetary Values
All monetary values are stored as integers representing cents (smallest currency unit). For example:
- 1,000,000 IRR = 1,000,000 (in database)
- Display: 1,000,000 / 100 = 10,000.00 IRR

### Loan Statuses
- `pending`: Awaiting admin approval
- `approved`: Approved by admin, ready to activate
- `rejected`: Rejected by admin
- `active`: Active loan with payment schedule
- `delinquent`: Has overdue payments
- `paid`: Fully paid off

### Installment Statuses
- `pending`: Not yet due
- `paid`: Payment completed
- `overdue`: Payment is past due date

## Rate Limiting
API endpoints are rate limited to prevent abuse. Default limits:
- Authentication endpoints: 5 requests per minute
- Other endpoints: 60 requests per minute

## Best Practices

### Using API Resources
1. **Always use API Resources** for consistent response formatting
2. **Eager load relationships** when needed: `Loan::with(['user', 'schedules'])`
3. **Use conditional relationships** to avoid N+1 queries: `$this->whenLoaded('user')`
4. **Include formatted values** for better frontend integration

### Statistics API
1. **Use LoanStatisticsService** instead of manual queries
2. **Clear cache** when data changes significantly
3. **Handle missing keys** gracefully with fallback values

### Error Handling
1. **Always check for missing keys** in arrays
2. **Use safe navigation** with `??` operator
3. **Provide meaningful error messages** in Persian and English

### Performance
1. **Cache expensive queries** like statistics
2. **Use pagination** for large datasets
3. **Limit relationship loading** to only what's needed

## Testing
Use the provided Postman collection or test the endpoints using tools like:
- Postman
- Insomnia
- curl
- HTTPie

## API Resources

The system uses Laravel API Resources for consistent JSON responses. All monetary values are automatically formatted for display while maintaining precision in the database.

### Resource Examples

#### Loan Resource Response
```json
{
    "success": true,
    "data": {
        "id": 1,
        "user_id": 1,
        "amount": 10000000,
        "formatted_amount": "100,000.00",
        "term_months": 12,
        "interest_rate": 14.5,
        "monthly_payment": 900000,
        "formatted_monthly_payment": "9,000.00",
        "remaining_balance": 10000000,
        "formatted_remaining_balance": "100,000.00",
        "status": "approved",
        "status_label": "تایید شده",
        "start_date": "2024-01-01",
        "approved_at": "2024-01-02T00:00:00.000000Z",
        "approved_by": 2,
        "rejection_reason": null,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-02T00:00:00.000000Z",
        "user": {
            "id": 1,
            "first_name": "John",
            "last_name": "Doe",
            "email": "john@example.com",
            "is_active": true,
            "role": "user",
            "role_name": "کاربر",
            "created_at": "2024-01-01T00:00:00.000000Z",
            "updated_at": "2024-01-01T00:00:00.000000Z"
        },
        "approved_by_user": {
            "id": 2,
            "first_name": "Admin",
            "last_name": "User",
            "email": "admin@loanmanagement.com",
            "is_active": true,
            "role": "admin",
            "role_name": "مدیر",
            "created_at": "2024-01-01T00:00:00.000000Z",
            "updated_at": "2024-01-01T00:00:00.000000Z"
        },
        "schedules": [
            {
                "id": 1,
                "loan_id": 1,
                "installment_number": 1,
                "amount_due": 900000,
                "formatted_amount_due": "9,000.00",
                "principal_amount": 800000,
                "formatted_principal_amount": "8,000.00",
                "interest_amount": 100000,
                "formatted_interest_amount": "1,000.00",
                "due_date": "2024-02-01",
                "status": "pending",
                "status_label": "در انتظار پرداخت",
                "paid_at": null,
                "penalty_amount": 0,
                "formatted_penalty_amount": "0.00",
                "created_at": "2024-01-02T00:00:00.000000Z",
                "updated_at": "2024-01-02T00:00:00.000000Z"
            }
        ],
        "payments": []
    }
}
```

#### Statistics Resource Response
```json
{
    "success": true,
    "data": {
        "total_loans": 15,
        "pending_loans": 5,
        "approved_loans": 3,
        "rejected_loans": 2,
        "active_loans": 2,
        "delinquent_loans": 1,
        "paid_loans": 1,
        "total_amount": 50000000,
        "formatted_total_amount": "500,000.00",
        "monthly_loans": 8,
        "monthly_amount": 25000000,
        "formatted_monthly_amount": "250,000.00"
    },
    "meta": {
        "generated_at": "2024-01-15T10:30:00.000000Z",
        "currency": "IRR"
    }
}
```

### Resource Features

1. **Automatic Formatting**: All monetary values include both raw integer values and formatted display values
2. **Localized Labels**: Status labels are provided in Persian
3. **Conditional Relationships**: Related data is only included when explicitly loaded
4. **Meta Data**: Additional metadata is included where relevant
5. **Consistent Structure**: All responses follow the same structure pattern
6. **Caching**: Statistics are cached for 5 minutes to improve performance
7. **Error Handling**: Safe fallback values (0) for missing statistics

### Statistics Service

The system includes a dedicated `LoanStatisticsService` that provides:

- **Comprehensive Statistics**: Complete loan statistics with all status counts
- **Caching**: 5-minute cache for improved performance
- **Monthly Statistics**: Current and previous month comparisons
- **Status-based Statistics**: Breakdown by loan status
- **Cache Management**: Methods to clear cache when needed

#### Usage Example
```php
use App\Services\LoanStatisticsService;

$statisticsService = new LoanStatisticsService();
$stats = $statisticsService->getStatistics();
```

## Sample Test Data
The system includes seeded data for testing:
- Admin user: `admin@loanmanagement.com` / `password123`
- Regular users: `user1@example.com` to `user10@example.com` / `password123`
- Various loan statuses and payment schedules