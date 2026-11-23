# Implementation status

## Delivered in this MVP scaffold
- PHP 8.2+ front controller and small router
- Environment configuration loader
- PDO MySQL connection
- CSRF-protected registration, login and logout
- Password hashing and session ID regeneration
- Customer assessment with server-side validation and service consent
- Assessment screening status flags for allergies / declared professional-review needs
- Customer dashboard and recent assessment list
- Role-protected admin dashboard with basic counts and recent records
- Public homepage, plans, BMI calculator, how-it-works, privacy draft and terms draft
- MySQL initial migration and package seed
- GitHub Actions PHP syntax check

## Not live / next implementation increments
1. Select payment provider and implement server-created orders, signed webhooks, idempotency and refunds.
2. Select AI provider/model and implement structured generation against a verified food database.
3. Build nutritionist queue, plan editor, approval and revision history.
4. Add PDF generation and private document download.
5. Add WhatsApp and email provider integration, templates and delivery logs.
6. Add admin CRUD, support tickets, affiliate tracking and financial reports.
7. Add rate limiting, email verification, password reset, 2FA for staff, automated backups and security review.

No real API credentials, payment provider, domain DNS, Hostinger access or GitHub repository were supplied. Nothing has been deployed and no live payment or AI requests are enabled.
