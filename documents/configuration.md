# Configuration

## Zarinpal Settings

Add your Zarinpal merchant ID to the `.env` file:

```env
ZARINPAL_MERCHANT_ID=your_merchant_id
ZARINPAL_SANDBOX=true
```

## System Settings

Default settings are seeded into the database and can be modified via the admin panel:

- **Default Interest Rate:** 14.5%
- **Penalty Rate:** 0.5% per day
- **Min Loan Amount:** 1,000,000 IRR
- **Max Loan Amount:** 100,000,000 IRR
- **Min Loan Term:** 3 months
- **Max Loan Term:** 36 months
