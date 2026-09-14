# ZATCA CSID Auto Fetch (Standalone)

This folder contains a standalone utility that fetches:

1. Compliance CSID using CSR + OTP
2. Production CSID automatically using the Compliance credentials

No existing application files are modified.

## Files

- `tools/zatca/ZatcaCsidAutoFetcher.php`: Reusable class for ZATCA CSID flow
- `tools/zatca/fetch-csids.php`: CLI runner
- `tools/zatca/config.example.json`: Example values

## Requirements

- PHP 7.4+
- Composer dependencies installed (`vendor/autoload.php` exists)
- Network access to ZATCA API endpoint

## Quick Run

```bash
php tools/zatca/fetch-csids.php --otp="123456" --csr="C:\\path\\to\\my-zatca.csr"
```

Optional:

```bash
php tools/zatca/fetch-csids.php --otp="123456" --csr="C:\\path\\to\\my-zatca.csr" --api-base="https://gw-fatoora.zatca.gov.sa/e-invoicing/developer-portal" --out="tools/zatca/latest-csids.json"
```

## Output

On success it prints these keys ready for app settings:

- `zatca_binary_security_token`
- `zatca_secret`
- `zatca_compliance_request_id`
- `zatca_production_binary_security_token`
- `zatca_production_secret`
- `zatca_production_request_id`

## Notes

- `--csr` accepts either a CSR file path or raw CSR content.
- CSR PEM headers/footers are normalized automatically.
- Production step runs only if Compliance step succeeds.
