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
- Server: `ssh root@app1` (short alias for app1.profectify.com)
- Web root: `/var/www/kompaza.com/public/`
- Deploy: `ssh root@app1 "cd /var/www/kompaza.com && git pull"`
- Database: `kompaza`
- Wildcard SSL via Let's Encrypt + Cloudflare DNS challenge

## Important Notes
- All UI text must be in English
- BREVO_API_KEY, STRIPE keys configured on server, not in git
- PDF files served via tokenized download links
- Rate limiting on login, signup, and API endpoints
- ConnectPilot respects daily LinkedIn limits (20 connections, 50 messages default)
