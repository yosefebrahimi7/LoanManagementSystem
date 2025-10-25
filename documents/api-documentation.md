# API Documentation

## Authentication Endpoints

### Register User
```http
POST /api/register
Content-Type: application/json

{
    "firstName": "John",
    "lastName": "Doe",
    "email": "john@example.com",
    "password": "password123"
}
```

### Login
```http
POST /api/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password"
}
```

### Logout
```http
POST /api/logout
Authorization: Bearer {token}
```

### Get Authenticated User
```http
GET /api/me
Authorization: Bearer {token}
```

## Loan Endpoints

### Create Loan Request
```http
POST /api/loans
Authorization: Bearer {token}
Content-Type: application/json

{
    "amount": 10000000,
    "term_months": 12,
    "interest_rate": 14.5,
    "start_date": "2025-11-01"
}
```

### Get User Loans
```http
GET /api/loans
Authorization: Bearer {token}
```

### Get Loan Details
```http
GET /api/loans/{id}
Authorization: Bearer {token}
```
