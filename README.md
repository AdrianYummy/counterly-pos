# CaféFlow

CaféFlow is a complete Laravel point-of-sale project for IT0049, designed for a modern café with drinks, food, packaged coffee, merchandise, and seasonal gifts. It manages products, customers, staff accounts, inventory-backed multi-product sales, and read-only transaction history through an authenticated, responsive interface.

## Submission details

- Student / group: _Complete before submission_
- Members and roles: _Complete before submission_
- Section: _Complete before submission_
- Professor: _Complete before submission_
- GitHub repository: _Create/paste the real URL before submission_
- Live website: _Deploy/paste the real URL before submission_

## Implemented features

- Username/password staff login, throttled failures, session regeneration, POST logout, browser-close/reload protection, 15-minute inactivity logout, and authentication on every management route
- Secure email-based password recovery with expiring, throttled, single-use reset tokens and non-enumerating responses
- Branded welcome email for newly created staff accounts, with no password or password hash included
- Live dashboard counts, Asia/Manila daily sales total, recent sales, low-stock list, and field-level accountability for product, stock, sale-driven stock, customer, and staff changes
- Role-based access with administrator and cashier accounts; administrator-only staff management, inventory changes, and customer deletion are enforced by middleware
- Product, customer, staff, transaction, and accountability lists limited to 10 records per page with numbered pagination
- Case-insensitive duplicate prevention for product names, staff full names, and customer names/emails, backed by database unique indexes
- GD-backed image validation, resize, WebP re-encoding, generated filenames, replacement cleanup, and local placeholders
- Multi-product cart with click-again deselection, remove controls, per-item quantities, an optional customer, exact centavo totals, atomic stock deduction, insufficient-stock rollback, and overflow protection
- Read-only sales history with search, product/staff/date filters and transaction details
- Optional customer receipt email after checkout, receipt resend from transaction details, and a print-ready receipt
- Public CaféFlow landing page with smooth section navigation, cursor-reactive coffee-bean physics, a bean sign-in transition, photo-backed login hero, persistent themes, labeled collapsible navigation, animated coffee-mug loading feedback, and reduced-motion support
- Stale-stock detection so an open product edit cannot undo a newer sale
- Restrictive foreign keys; transaction history blocks deletion of referenced products, customers, or staff
- Current-account, final-staff, and final-administrator safeguards
- UUID public references in routes and forms so internal numeric database keys are not exposed in HTTP addresses
- One-way password hashing, strong new-password rules, login throttling, CSRF protection, session regeneration, escaped output, and browser security headers
- Seed data: 10 products, 5 fictional customers, and 2 staff accounts
- Vite source and committed production assets for hosts without Node.js

The required model is intentionally one product type per sale row. Carts, payment gateways, VAT, discounts, refunds, barcode scanning, and public registration remain outside this assignment's baseline.

## Tested environment

| Component | Tested version |
| --- | --- |
| Laravel | 12.74.0 |
| PHP | 8.2.12 (XAMPP) |
| Database | MariaDB 10.4.32 (MySQL compatible) |
| Composer | 2.10.3 |
| Node.js / npm | 26.13.1 / 11.20.0 |
| Image driver | PHP GD, output WebP |

Required PHP extensions include Ctype, cURL, DOM, Fileinfo, Filter, GD, Hash, Mbstring, OpenSSL, PCRE, PDO, `pdo_mysql`, Session, Tokenizer, XML, and XMLWriter. Laravel 12 is used because this machine has PHP 8.2; Laravel 13 requires PHP 8.3 or newer.

## Windows/XAMPP setup

1. Start Apache (optional if using Artisan) and MySQL from XAMPP.
2. Create an empty database:

   ```sql
   CREATE DATABASE counterly_pos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. From this project folder, install dependencies and create local settings:

   ```powershell
   composer install
   Copy-Item .env.example .env
   php artisan key:generate
   ```

4. Set the real `DB_*` values in `.env`. Keep these baseline settings:

   ```dotenv
   SESSION_DRIVER=file
   SESSION_EXPIRE_ON_CLOSE=true
   SESSION_IDLE_TIMEOUT=900
   SESSION_HEARTBEAT_TIMEOUT=180
   CACHE_STORE=file
   QUEUE_CONNECTION=sync
   DISPLAY_TIMEZONE=Asia/Manila
   ```

5. Set a private initial password in `.env` before seeding:

   ```dotenv
   DEMO_STAFF_PASSWORD=YourPrivateDemoPassword
   ```

   New passwords require 10–72 characters with uppercase, lowercase, number, and symbol characters.

6. Email is safe to test locally with `MAIL_MAILER=log`; messages appear in `storage/logs/laravel.log`, and `MAIL_PREVIEW_LINK=true` displays a local reset link after a valid request. For real inbox delivery, configure SMTP credentials in `.env` and set `MAIL_PREVIEW_LINK=false`:

   ```dotenv
   MAIL_MAILER=smtp
   MAIL_PREVIEW_LINK=false
   MAIL_HOST=your-smtp-host
   MAIL_PORT=587
   MAIL_USERNAME=your-smtp-username
   MAIL_PASSWORD=your-private-smtp-password
   MAIL_SCHEME=null
   MAIL_FROM_ADDRESS=noreply@your-domain.example
   MAIL_FROM_NAME="CaféFlow"
   ```

   Alternatively, CaféFlow includes the official Resend API SDK. After verifying a sending domain and creating a restricted API key, use:

   ```dotenv
   MAIL_MAILER=resend
   RESEND_API_KEY=re_your_private_key
   MAIL_PREVIEW_LINK=false
   MAIL_FROM_ADDRESS=noreply@your-verified-domain.example
   MAIL_FROM_NAME="CaféFlow"
   ```

   Keep `MAIL_MAILER=log` until all Resend values are ready, and never commit the real API key.

7. Install/build assets, migrate, seed, and expose prepared uploads:

   ```powershell
   npm ci
   npm run build
   php artisan migrate
   php artisan db:seed
   php artisan storage:link
   ```

8. Start the application:

   ```powershell
   php artisan serve
   ```

   Open `http://127.0.0.1:8000`. For source asset hot reload, run `npm run dev` in a second terminal. A production build is already included under `public/build`.

Seeded usernames are `admin` and `cashier`, with placeholder recovery addresses `admin@cafeflow.test` and `cashier@cafeflow.test`. Both use the password set in `DEMO_STAFF_PASSWORD`. Replace the placeholder addresses in **User Accounts** before testing real password delivery. Change assessor credentials through a private submission channel—never commit a real password. The seeder uses stable usernames/emails and can be rerun without silently duplicating demo records, but it must not be run automatically on production redeployments.

## Design and security notes

The schema keeps signed `INT` primary/foreign keys internally for efficient joins and adds unique UUID public identifiers for URLs and submitted record references. It uses `DECIMAL(10,2)` money, created-at-only timestamps, nullable `sales.customer_id`, and restrictive foreign keys. Hiding internal IDs is defense-in-depth, not authorization: admin middleware independently rejects prohibited cashier requests with HTTP 403.

Passwords use Laravel's configured one-way hash and are hidden from serialization. New staff passwords require 10–72 characters with uppercase, lowercase, number, and symbol characters. Models expose explicit fillable fields; controllers never accept client totals, seller IDs, or timestamps. Blade escapes user content, Form Requests validate writes, Laravel CSRF middleware remains enabled, and login success regenerates the session ID.

Uploaded files are limited to valid JPG, PNG, or WebP images up to 4 MB and 6000×6000 pixels. The application decodes them with GD, scales within 1200×1200 (avatars 600×600), re-encodes to WebP, and stores only relative paths under `storage/app/public/products` or `storage/app/public/avatars`. `public/storage` must remain linked to `storage/app/public`; production storage must persist across releases.

Application timestamps remain UTC. Display and local-day dashboard boundaries use Asia/Manila. Session cookies are HttpOnly, SameSite=Lax, and browser-session-only by default. Authenticated pages send a short heartbeat, expire after 15 minutes without user activity, and close the server session when a tab closes, reloads, or is restored. The server rejects a missing heartbeat even when a crash prevents the close signal. Set `SESSION_SECURE_COOKIE=true`, `APP_URL=https://...`, `APP_DEBUG=false`, and `APP_ENV=production` on HTTPS hosting.

## Verification

```powershell
php artisan route:list
php artisan test
composer check-platform-reqs
npm run build
```

The latest local run passed **41 tests / 280 assertions**, the Vite production build, Composer platform checks, MariaDB migrations/seeders, and authenticated HTTP smoke checks. See [docs/TESTING.md](docs/TESTING.md) for executed versus pending checks.

Additional guides:

- [Schema and business rules](docs/SCHEMA.md)
- [User guide](docs/USER_GUIDE.md)
- [Testing record](docs/TESTING.md)
- [Deployment and submission](docs/DEPLOYMENT.md)

## Known external actions

No GitHub account, repository destination, hosting account, domain, or production database credentials were available in the build environment. The source is deployable, but a real repository URL and hosted URL must be created and verified before submission. Do not claim those rubric items until their checks in `docs/TESTING.md` pass.
