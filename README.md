# FITMEAL INDIA – PHP MVP

A PHP 8.2+ / MySQL 8 starter application for the FITMEAL INDIA meal-planning service.

## Included in this delivery

- Responsive Hindi-first public landing page, plans page and BMI educational calculator
- Customer registration, login, logout and session-based authentication
- Multi-step-ready customer assessment form with server-side validation and consent record
- Customer dashboard showing recent assessments
- Role-protected admin dashboard with customer, order and assessment counts
- Initial MySQL migration and product seed data
- GitHub Actions PHP syntax-check workflow
- Environment template; no real credentials are included

## Not yet implemented / deliberately disabled

- Payment gateway checkout and webhooks: provider not selected. No payment is collected.
- Live AI generation: provider/model and credentials not configured. Do not treat this scaffold as generating plans.
- Nutritionist editing/approval UI, PDF generation, WhatsApp delivery, affiliate management, refunds, subscription billing and full admin CRUD are next development modules.
- Privacy, terms and refund pages need legal review and final content before public launch.

## Requirements

- PHP 8.2+
- MySQL 8.0+
- PHP extensions: PDO, pdo_mysql, JSON
- HTTPS in production

## Local setup

1. Copy `.env.example` to `.env` and configure database values.
2. Create a MySQL database named `fitmealindia` (or update `.env`).
3. Import `database/migrations/001_initial_schema.sql`.
4. Import `database/seeds/001_products.sql`.
5. Set the web document root to this project's `public/` directory.
6. Start locally with `php -S 127.0.0.1:8000 -t public`.
7. Open `http://127.0.0.1:8000`.

For local testing, create the first admin safely using SQL after registering a normal account:

```sql
UPDATE users SET role='admin' WHERE email='your-registered-email@example.com';
```

Do not leave public admin registration enabled. This project does not ship with a default admin password.

## Hostinger deployment

- Upload the project so the web root points to `public/`.
- Keep `.env`, `app/`, `database/`, `storage/` and `vendor/` outside the public document root where possible.
- If your hosting plan requires `public_html` as the document root, place only the contents of `public/` there and keep the application directories outside `public_html`; adjust `public/index.php` to the actual private project path.
- Set `APP_ENV=production`, `APP_DEBUG=false`, strong `APP_KEY`, database credentials and HTTPS.
- Ensure `storage/logs` and `storage/private` are writable by the PHP process but not publicly accessible.
- Use a least-privilege database user, daily backups and test restore procedures.

## Security notes

- Passwords use PHP `password_hash()` / `password_verify()`.
- PDO uses prepared statements.
- POST forms use CSRF tokens.
- Admin routes enforce role checks.
- Payment status must never be trusted from a browser redirect; implement signed server-side webhooks and idempotency before enabling checkout.
- Do not store card data or secrets in source control.
- This is an MVP scaffold, not a security-audited production system.

## Source control

The project is maintained in the GitHub repository: https://github.com/dongreprafull/fitmealindia

Never commit `.env` or production credentials.
