# Development Guide

## Project Structure

```
web-api/
├── app/
│   ├── Models/         # Eloquent models
│   ├── Services/       # Business logic
│   ├── Repositories/   # Data access layer
│   ├── Policies/       # Authorization policies
│   ├── Http/
│   │   ├── Controllers/    # API controllers
│   │   └── Requests/       # Form request validation
│   ├── Events/         # Domain events
│   └── Jobs/           # Background jobs
├── database/
│   ├── migrations/     # Database migrations
│   ├── seeders/        # Database seeders
│   └── factories/      # Model factories
└── tests/              # Pest tests
```
