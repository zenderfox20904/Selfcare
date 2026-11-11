# Self Care — localhost setup guide

This is a step-by-step guide for running Self Care on a local machine. The short version is in the root [README.md](../README.md).

## What you need

- PHP 8.1 or later (8.3 works), Composer 2, and MySQL 5.7+ / 8.x or MariaDB 10.x
- PHP extensions: `bcmath`, `ctype`, `curl`, `fileinfo`, `gd`, `intl`, `json`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `tokenizer`, `xml`, `zip`
- Node.js 18+ only if you plan to rebuild frontend assets
- A domain-style URL: `http://localhost:8000`, `http://selfcare.test`, or a similar virtual host. A subfolder URL such as `http://localhost/selfcare` is not supported.

## 1. Clone

```bash
git clone https://github.com/zenderfox20904/Selfcare.git
cd Selfcare
```

## 2. PHP packages

```bash
composer install
```

If Composer complains about the PHP version, switch the CLI to 8.1, 8.2, or 8.3 (`php -v` to confirm).

## 3. Environment

```bash
cp .env.example .env
php artisan key:generate
```

Create an empty database:

```sql
CREATE DATABASE selfcare CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Edit `.env` and set at least:

```
APP_NAME="Self Care"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=selfcare
DB_USERNAME=root
DB_PASSWORD=your_database_password
MAIL_DRIVER=log
QUEUE_CONNECTION=sync
```

Leave API keys and SMTP passwords empty until you need those services.

## 4. Import the database

The product is installed from a SQL dump, not from `php artisan migrate --seed`.

Clean install (recommended first run):

```bash
mysql -u root -p selfcare < database/hms_saas.sql
```

Optional demo dataset (sample hospitals and staff):

```bash
mysql -u root -p selfcare < database/dummy-hms-saas.sql
```

Import into an empty database. Do not import both dumps into the same schema. After a dump import, skip `php artisan migrate` unless you are applying a later official upgrade.

## 5. Storage and permissions

```bash
php artisan storage:link
chmod -R ug+rwx storage bootstrap/cache
```

On Windows, make sure the `storage` and `bootstrap/cache` folders are writable by PHP.

## 6. Assets

Pre-built assets already live under `public/` (including Self Care CSS at `public/css/selfcare-brand.css`, the logo, and the favicon). You can start the server without npm.

To rebuild after changing `resources/` or `webpack.mix.js`:

```bash
npm install
npm run dev
```

Use `npm run prod` for minified production assets.

## 7. Run the server

Using Laravel's built-in server:

```bash
php artisan serve
```

Visit http://localhost:8000.

Using a virtual host (XAMPP, WAMP, Laravel Herd, or Valet):

- Document root must be the `public/` directory of this project.
- Server name example: `selfcare.test`.
- Set `APP_URL` in `.env` to that exact origin, including the scheme (`http://selfcare.test`).
- Add that hostname to `central_domains` in `config/tenancy.php` (`localhost` and `127.0.0.1` are already listed).

## 8. Sign in and brand the install

The SQL dump creates the original InfyHMS demo accounts so you can reach the panel on a fresh install. Treat them as temporary:

1. Sign in, then immediately change the demo password or create your own super admin user.
2. Open Super Admin → Settings and set:
   - Application name: `Self Care`
   - Logo: `web/img/selfcare-logo.png`
   - Favicon: `web/img/selfcare-favicon.ico`
3. Create a subscription plan if you need one (price and duration are editable; you can add a free plan).
4. Register a test hospital and open `http://localhost:8000/h/{username}` to confirm the hospital slug works.

Never commit `.env` or real passwords.

## 9. Mail, queues, and cron

- Local mail: `MAIL_DRIVER=log` writes messages to `storage/logs`.
- Real SMTP: set `MAIL_DRIVER=smtp` plus `MAIL_HOST`, `MAIL_PORT` (usually 587), `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION=tls`, and the from address. Then run `php artisan config:clear`.
- Queues: `QUEUE_CONNECTION=sync` needs no worker. For `database` or `redis`, run `php artisan queue:work`.
- Scheduler: in an environment that should run scheduled jobs, add:

```
* * * * * cd /path/to/Selfcare && php artisan schedule:run >> /dev/null 2>&1
```

## 10. Common problems

| Problem | What to try |
| --- | --- |
| "No application encryption key" | `php artisan key:generate` then `php artisan config:clear` |
| Database connection error | Recheck `.env` database values and that the schema exists |
| Missing styles | Confirm the document root is `public/`. Rebuild with `npm run dev` if you removed `public/assets` or `public/css` |
| `route:cache` error about `password.email` | Known duplicate route name: `Auth::routes()` in `routes/web.php` and a route in `routes/api.php` share the name. Skip `route:cache` or rename the API route |
| `/h/{username}` returns 404 | The hospital username must exist; register the hospital first |
| Cannot write logs or uploads | Make `storage` and `bootstrap/cache` writable; run `php artisan storage:link` |
| SMTP timeouts | Try port 587 with TLS. Many networks block outbound port 25 |

## Branding files

Logo and favicon masters are in [docs/branding](branding/). The live copies used by the app are:

- `public/web/img/selfcare-logo.png`
- `public/web/img/selfcare-mark.png`
- `public/web/img/selfcare-favicon.ico`
- `public/favicon.ico`, `public/favicon.png`
- `public/css/selfcare-brand.css`
- `public/site.webmanifest`
