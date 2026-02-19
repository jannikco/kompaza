# Kompaza

Multi-tenant SaaS platform for content marketing, lead generation, and LinkedIn automation.

## Setup

```bash
composer install
cp .env.example .env
# Edit .env with your database credentials
mysql -e "CREATE DATABASE kompaza CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql kompaza < database/schema.sql
```

## Development

```bash
cd public && php -S localhost:8000
```

## Default Login

- **Superadmin:** admin@kompaza.com / password (change immediately)
