# Self Care

Self Care is a multi-hospital management system. Hospitals can register, subscribe to a plan, and run their own branded site at a unique slug, while a master (super admin) account manages the platform.

This repository is the Self Care branded application based on **Self Care** credits & licences

## Features

- Super admin panel for hospitals, subscription plans, transactions, and landing-page content
- Per-hospital admin panel covering staff, patients, appointments, billing, pharmacy, labs, beds, blood bank, reports, and more
- IPD and OPD workflows (visits, diagnoses, prescriptions, charges, payments, timelines)
- Hospital public pages at `/h/{username}` (about, services, doctors, appointments, contact)
- Subscription plans with editable price and duration, including a free plan
- Multi-currency and multi-language support
- Optional payment gateways: Stripe, PayPal, Razorpay, Paystack, Paytm, Flutterwave, PhonePe
- Optional SMS (Twilio), Zoom live consultations, and Google reCAPTCHA
- Export to Excel in most list modules

## Tech stack

| Component | Version |
| --- | --- |
| PHP | 8.1 or later (8.3 works) |
| Laravel | 10.x |
| Livewire | 3.x |
| Tenancy | stancl/tenancy 3.7 (single database, `tenant_id`) |
| Database | MySQL 5.7+ / 8.x or MariaDB 10.x |
| Frontend | Laravel Mix, Bootstrap, jQuery |

Other notable packages include Spatie Permission and Media Library, Laravel Sanctum, and the payment SDKs listed above.

## Requirements

- PHP 8.1+ with extensions: `bcmath`, `ctype`, `curl`, `fileinfo`, `gd`, `intl`, `json`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `tokenizer`, `xml`, `zip`
- Composer 2.x
- MySQL or MariaDB
- Node.js 18+ and npm (only if you change frontend assets; compiled assets are already in `public/`)
- Optional PHP.ini increases for large uploads: `upload_max_filesize=50M`, `post_max_size=100M`, `max_file_uploads=50`

Self Care must be served from a domain, subdomain, or `php artisan serve`. It will **not** work correctly from a subfolder such as `http://localhost/selfcare`.

## Run on localhost

A longer version of these steps is in [docs/localhost-setup.md](docs/localhost-setup.md).

### 1. Clone the repository

```bash
git clone https://github.com/zenderfox20904/Selfcare.git
cd Selfcare
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Environment file and app key

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Create a database

In MySQL or MariaDB, create an empty database, for example:

```sql
CREATE DATABASE selfcare CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Then edit `.env`:

```
APP_NAME="Self Care"
APP_URL=http://localhost:8000
DB_DATABASE=selfcare
DB_USERNAME=root
DB_PASSWORD=your_database_password
```

Keep `MAIL_DRIVER=log` for local work so email is written to `storage/logs` instead of being sent.

### 5. Import the database

The official product install uses a SQL dump rather than `php artisan migrate --seed`.

From a MySQL client (or phpMyAdmin):

```bash
mysql -u root -p selfcare < database/hms_saas.sql
```

- `database/hms_saas.sql` — clean install (platform + super admin only)
- `database/dummy-hms-saas.sql` — optional demo data with sample hospitals and staff

Do **not** run `php artisan migrate` against a freshly imported dump unless you are following an upgrade guide. The dump already includes the migration history.

### 6. Storage link and permissions

```bash
php artisan storage:link
chmod -R ug+rwx storage bootstrap/cache
```

### 7. Frontend assets

Compiled CSS, JavaScript, and images already ship in `public/`. You can skip npm for a first run.

If you edit files under `resources/assets` or `webpack.mix.js`:

```bash
npm install
npm run dev      # or: npm run prod
```

### 8. Start the app

```bash
php artisan serve
```

Open [http://localhost:8000](http://localhost:8000).

On Windows, XAMPP/WAMP users should create a virtual host whose document root is the `public/` directory, for example `selfcare.test`. Do not point the vhost at the project root or at a subfolder URL. If you use a custom hostname other than `localhost` or `127.0.0.1`, add it to `central_domains` in `config/tenancy.php`.

### 9. First login

Importing `database/hms_saas.sql` creates the original product demo accounts. They are starter accounts only.

- Create your own super admin (or change the demo account password) before using the system for anything real.
- After login, open Super Admin → Settings and set the application name to **Self Care**, the logo to `web/img/selfcare-logo.png`, and the favicon to `web/img/selfcare-favicon.ico`.
- Register a test hospital from the landing page (or from the super admin panel) and confirm its public pages at `/h/{username}`, where `{username}` is the hospital slug chosen at registration.

Do not use production passwords in `.env` or in this repository.

## Hospital slug URLs

Each registered hospital has a public site at:

```
http://localhost:8000/h/{username}
```

Examples: `/h/city-hospital`, `/h/demo`. The hospital admin signs in at the main login page; tenancy is resolved from the hospital username/slug.

## Mail / SMTP

For local development, leave `MAIL_DRIVER=log`.

To send real email, set these in `.env` (and in Super Admin mail settings if the panel overrides them):

```
MAIL_DRIVER=smtp
MAIL_HOST=mail.example.com
MAIL_PORT=587
MAIL_USERNAME=your_smtp_username
MAIL_PASSWORD=your_smtp_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

Port 587 with STARTTLS is the usual submission port. Outbound port 25 is often blocked by hosts. After changing mail settings, run `php artisan config:clear`.

## Queues and the scheduler

`.env.example` uses `QUEUE_CONNECTION=sync`, so jobs run inline and you do not need a queue worker for a basic local setup.

If you switch the queue to `database` or `redis`, run a worker:

```bash
php artisan queue:work
```

Scheduled tasks (subscriptions, reminders, and similar) need the Laravel scheduler. Add this cron entry in production:

```
* * * * * cd /path/to/Selfcare && php artisan schedule:run >> /dev/null 2>&1
```

## Production deploy notes

- Point the Apache (or nginx) document root at the `public/` directory, not the project root.
- This app was deployed on Apache with aaPanel. After any vhost change, test the config (`apachectl -t`) before a graceful reload.
- Enable HTTPS (for example Let's Encrypt) and redirect HTTP to HTTPS.
- Set `APP_ENV=production`, `APP_DEBUG=false`, and `APP_URL` to your public HTTPS URL.
- Set `chmod -R ug+rwx storage bootstrap/cache` and keep the web user as the owner of those paths.
- Install the cron entry above for `schedule:run`.
- Do not commit `.env`. Keep database, mail, and payment credentials only on the server.
- Avoid `php artisan route:cache` until you have applied a fix for the duplicate route name described below.

## Troubleshooting

**500 error or "No application encryption key"**  
Run `php artisan key:generate` and confirm `APP_KEY` is set in `.env`. Then `php artisan config:clear`.

**SQLSTATE connection refused**  
Check `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`. Confirm the database exists and the user can access it.

**Blank pages / missing CSS**  
The document root must be `public/`. Compiled assets live in `public/css`, `public/js`, `public/backend`, and `public/assets`. If you deleted them, run `npm install && npm run prod`.

**`php artisan route:cache` fails with a duplicate route name `password.email`**  
Laravel UI registers `password.email` via `Auth::routes()` in `routes/web.php`, and `routes/api.php` also names a route `password.email`. That clash is harmless at runtime but blocks `route:cache`. Use `php artisan route:clear` and skip route caching, or rename the API route before caching.

**Hospital slug page 404**  
The hospital must be registered and its username/slug must match the URL: `/h/{username}`. The `setTenantFromUsername` middleware resolves the tenant from that segment.

**Permission denied writing logs or uploads**  
`chmod -R ug+rwx storage bootstrap/cache` and ensure `php artisan storage:link` has been run.

**Mails not sending**  
With `MAIL_DRIVER=log`, check `storage/logs`. For SMTP, confirm port 587 is open, credentials are correct, and `php artisan config:clear` has been run. Some hosts block outbound port 25.

**`composer install` fails on PHP version**  
Use PHP 8.1 or newer (8.3 is fine). Check with `php -v`.

## Project layout

```
app/                Application code (HTTP, Livewire, models, repositories)
bootstrap/          Framework bootstrap and cache placeholders
config/             Laravel and package configuration
database/           Migrations, seeders, and official SQL dumps
docs/               Local setup guide and Self Care branding artwork
lang/               Translations
public/             Web root (index.php, assets, Self Care logo and favicon)
resources/          Views and frontend sources
routes/             web.php, api.php, and related route files
storage/            Logs, cache, sessions, and currency JSON
tests/              PHPUnit tests
```

## Credits & licences

Laravel, Livewire, stancl/tenancy, Spatie packages, and the bundled payment and UI libraries remain under their own licences (MIT and others). Licence comment files such as `public/js/*.LICENSE.txt` are retained. developed by **Arifuzzaman Raihan**
