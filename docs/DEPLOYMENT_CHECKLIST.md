# Hostinger deployment checklist

- [ ] Confirm domain ownership and DNS for fitmealindia.com.
- [ ] Confirm the Hostinger plan supports PHP 8.2+, MySQL 8-compatible features, PDO MySQL and cron.
- [ ] Point document root to `public/` where supported.
- [ ] If document root must be `public_html`, keep `app/`, `config/`, `database/`, `storage/` and `.env` outside it; put only public assets and front controller in the web root and adjust bootstrap path.
- [ ] Create a dedicated least-privilege MySQL user and database.
- [ ] Import `database/migrations/001_initial_schema.sql` then `database/seeds/001_products.sql`.
- [ ] Copy `.env.example` to `.env`; set production values and a long random APP_KEY.
- [ ] Set APP_ENV=production and APP_DEBUG=false.
- [ ] Enforce HTTPS and secure session cookies.
- [ ] Verify private storage and logs are not web-accessible.
- [ ] Create first admin by registering the account and promoting that account through a controlled SQL operation; remove or restrict DB credentials afterwards.
- [ ] Test registration, login, assessment, admin role restrictions and database backup/restore.
- [ ] Do not enable paid checkout until a provider is selected and signed webhook tests pass.
- [ ] Do not enable AI plan delivery until professional review workflow and verified food data are implemented.
- [ ] Complete privacy notice, terms, refund policy and legal entity contact details before accepting real customer data/orders.
