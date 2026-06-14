# Kompaza (kompaza.com)

## Project Overview
Multi-tenant SaaS platform combining content marketing, lead generation, order management, customer management, and LinkedIn automation (ConnectPilot). Each customer (tenant) gets their own subdomain (`company.kompaza.com`) or custom domain. All UI text is in **English**.

## Tech Stack
- **Backend:** PHP 8.2+, custom MVC (same pattern as PrintWorks/connect2print)
- **Database:** MariaDB/MySQL 8.0+
- **Frontend:** Tailwind CSS (CDN), Alpine.js (CDN), Quill v2 (CDN for rich text)
- **Dependencies:** Composer with only `vlucas/phpdotenv`
- **Server:** nginx + PHP-FPM on app1.profectify.com
- **Email:** Brevo API (raw cURL, no SDK)
- **Payments:** Stripe API (raw cURL, no SDK)

## Architecture
- Single entry point: `public/index.php` (multi-mode router)
- Three routing modes based on HTTP_HOST:
  - **Marketing** (kompaza.com) → `src/Controllers/marketing/`
  - **Superadmin** (superadmin.kompaza.com) → `src/Controllers/superadmin/`
  - **Tenant** ({slug}.kompaza.com or custom domain) → `src/Controllers/shop/` + `src/Controllers/admin/`
- Tenant resolution: `src/Services/TenantResolver.php`
- Controllers: `src/Controllers/` (flat PHP files, loaded by router)
- Views: `src/Views/` (PHP templates with ob_start/ob_get_clean pattern)
- Models: `src/Models/` (static methods, PDO)
- Database: `src/Database/Database.php` (PDO singleton)
- Auth: `src/Auth/Auth.php` (multi-role: superadmin, tenant_admin, customer)
- Config: `src/Config/config.php` (loads .env, defines constants)
- Helpers: `src/Config/functions.php` (global helpers)

## Key Design Decisions
- **Multi-tenant via subdomain**: TenantResolver resolves tenant from HTTP_HOST
- **All user roles in one table**: `users` table with role ENUM (superadmin, tenant_admin, customer)
- **Per-tenant content**: All content tables have tenant_id FK
- **Per-tenant integrations**: Tenants can configure their own Brevo/Stripe keys
- **ConnectPilot**: Browser automation via LinkedIn session cookie (li_at)
- **Local file storage**: PDFs in `storage/pdfs/{tenant_id}/`, images in `public/uploads/{tenant_id}/`

## URL Structure
### Marketing (kompaza.com)
- `/` - Landing page
- `/pricing` - Pricing plans
- `/register` - Tenant registration
- `/login` - Login redirect

### Superadmin (superadmin.kompaza.com)
- `/` - Dashboard
- `/tenants` - Tenant management
- `/plans` - Plan management
- `/settings` - Platform settings

### Tenant ({slug}.kompaza.com)
- `/` - Shop homepage
- `/blog`, `/blog/{slug}` - Articles
- `/eboger`, `/ebog/{slug}` - Ebooks
- `/lp/{slug}` - Lead magnet landing page
- `/produkter`, `/produkt/{slug}` - Products
- `/kurv` - Cart
- `/checkout` - Checkout
- `/login`, `/registrer` - Customer auth
- `/konto` - Customer account
- `/admin` - Tenant admin dashboard
- `/admin/lead-magnets` - Lead magnet management
- `/admin/artikler` - Article management
- `/admin/eboger` - Ebook management
- `/admin/kunder` - Customer management
- `/admin/ordrer` - Order management
- `/admin/produkter` - Product management
- `/admin/connectpilot` - ConnectPilot LinkedIn automation
- `/admin/indstillinger` - Settings

## Deployment
- **Server access: `ssh root@app2`** (ssh config `Host app2` → `46.225.234.17`; the box's
  internal hostname is `app1`). NOTE: the `app1` / `app1.profectify.com` alias is
  **firewalled** — ports 22 and 3306 are blocked from outside, so `ssh root@app1` and
  remote MySQL to `app1` both time out. Use `root@app2`.
- Web root: `/var/www/kompaza.com/` (public docroot `/var/www/kompaza.com/public/`)
- Database: `kompaza` on **MariaDB 11.8**. As root on the server, `mysql kompaza` works
  via socket auth (no password). PHP runtime is **8.4** (`php8.4-fpm`), not 8.2.
- First time as root: `git config --global --add safe.directory /var/www/kompaza.com`
- **The server cannot `git pull` from GitHub** — its remote is `git@github-kompaza:...`
  but the `github-kompaza` SSH host alias is missing from root's `~/.ssh/config`
  ("Could not resolve hostname"). Until that's fixed, deploy by shipping a git bundle:
  ```bash
  # local: git bundle create /tmp/k.bundle <prevHEAD>..main   (or full: ... main)
  # scp /tmp/k.bundle root@app2:/tmp/
  # server: cd /var/www/kompaza.com && git fetch /tmp/k.bundle main && git merge --ff-only FETCH_HEAD
  ```
  Then: `composer dump-autoload -o`, `chown -R www-data:www-data storage/ public/uploads/`,
  `systemctl restart php8.4-fpm`. (`update_repo.sh` is stale: it `git pull`s and restarts
  `php8.2-fpm` — both wrong here.)
- **Migrations** are applied manually. Run `bash scripts/apply-launch-migrations.sh kompaza`
  on the server (MYSQL_OPTS for explicit creds). ALWAYS `mysqldump` a backup first.
  Heads-up: prod `orders.payment_method` is `VARCHAR(50)` (legacy data includes `stripe`),
  and `orders.status` is `VARCHAR` — do not narrow these to ENUMs.
- Wildcard SSL via Let's Encrypt + Cloudflare DNS challenge

## Important Notes
- All UI text must be in English
- BREVO_API_KEY, STRIPE keys configured on server, not in git
- PDF files served via tokenized download links
- Rate limiting on login, signup, and API endpoints
- ConnectPilot respects daily LinkedIn limits (20 connections, 50 messages default)
