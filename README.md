# Vaishnavi Tours — 24/7 Cab & Car Rental Service

A production-ready full-stack Laravel application powering **Vaishnavi Tours**, Bilaspur, Chhattisgarh. The platform provides a public booking engine, customer self-service portal, and comprehensive administrative dispatch panel with real-time fleet, driver, trip, and billing management.

---

## Tech Stack & Architecture

- **Backend Framework**: Laravel 11 / 13 (PHP 8.3+)
- **Frontend**: Blade, Vanilla CSS (Design System Tokens), Vite, JavaScript
- **Database**: MySQL 8+ / MariaDB (utf8mb4 charset & collation)
- **Container Server**: FrankenPHP (Caddy-based) via `Dockerfile.vercel`
- **Hosting Platform**: Vercel Fluid Compute & Functions
- **Object Storage Ready**: Local / AWS S3 / Cloudflare R2 (`FILESYSTEM_DISK`)

---

## Local Development Setup

### 1. Prerequisites
- **PHP**: 8.3 or higher with extensions: `pdo_mysql`, `bcmath`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `curl`, `zip`, `gd`.
- **Composer**: 2.x
- **Node.js**: 20.x or higher & npm
- **MySQL**: 8.0+ running on port 3306

### 2. Installation
Clone the repository and install backend and frontend dependencies:
```bash
# Clone the repository
git clone https://github.com/YOUR_ORGANIZATION/vaishnavi-tours.git
cd vaishnavi-tours

# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### 3. Environment Configuration
Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```
Generate an application key:
```bash
php artisan key:generate
```

Configure your local database credentials in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vaishnavi_tours
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Database Migrations & Seeds
Run database migrations and seed baseline data:
```bash
# Run migrations
php artisan migrate

# Seed baseline data + demo accounts for local testing
php artisan db:seed
```

### 5. Compile Assets & Start Dev Server
```bash
# Build frontend assets
npm run build

# Start local server
php artisan serve
```
Visit the application at `http://127.0.0.1:8000`.

---

## Admin Login Credentials (Demo/Local)

| Role | Email | Password | Access URL |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@vaishnavitours.com` | `Password@123` | `/admin` or `/login` |
| **Demo Customer** | `rajesh.sharma@gmail.com` | `Password@123` | `/login` |

> [!CAUTION]
> For production environments, configure `ADMIN_EMAIL` and a strong `ADMIN_PASSWORD` in your Vercel Project Environment Variables.

---

## Database Management & Seeders

### Migrations
- **Development**:
  ```bash
  php artisan migrate
  ```
- **Production**:
  ```bash
  php artisan migrate --force
  ```
  *(Never use `migrate:fresh` in production, as it destroys customer and booking data).*

### Seeders Separation
- **Production**:
  ```bash
  php artisan db:seed --class=ProductionSeeder --force
  ```
  Seeds only essential company configuration (`SettingSeeder`) and tariff rates (`RateSeeder`).
- **Development / Demo**:
  ```bash
  php artisan db:seed --class=DemoSeeder
  ```
  Seeds sample vehicles, drivers, test customer accounts, and simulated booking records.

---

## Production Deployment on Vercel

The application includes dual-mode deployment compatibility:
1. **Container Deployment via `Dockerfile.vercel`** (Recommended):
   - Uses multi-stage OCI build: Node 20 for Vite assets, Composer 2 for optimized packages, and `dunglas/frankenphp:1-php8.3-alpine`.
   - Listens dynamically on `:{$PORT:80}` using `Caddyfile`.
2. **Serverless Functions via `api/index.php` & `vercel.json`**:
   - Zero configuration fallback with `/tmp/storage` support.

### Essential Vercel Environment Variables
Set these in **Vercel Dashboard → Settings → Environment Variables**:

```env
APP_NAME="Vaishnavi Tours"
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_GENERATED_32_BYTE_KEY
APP_URL=https://your-domain.vercel.app

# External MySQL Database (NEVER localhost / 127.0.0.1)
DB_CONNECTION=mysql
DB_HOST=external-mysql.provider.com
DB_PORT=3306
DB_DATABASE=vaishnavi_tours
DB_USERNAME=your_user
DB_PASSWORD=your_password

SESSION_DRIVER=cookie
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public
LOG_CHANNEL=stderr

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your_smtp_user
MAIL_PASSWORD=your_smtp_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="bookings@vaishnavitours.com"
MAIL_FROM_NAME="Vaishnavi Tours"
```

For a comprehensive 14-step runbook, refer to [VERCEL_DEPLOYMENT.md](VERCEL_DEPLOYMENT.md).

---

## Verification & Automated Testing

Run the automated test suites:
```bash
# Automated Phase 3 & 4 business logic, authorization & workflow verification
php tests/phase3_verification.php

# Check production route list
php artisan route:list

# Verify caching compatibility
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize:clear
```

---

## Brand Assets & Support

- **Office Address**: B.N City Colony, Jonki Road, Mangla Chowk, Bilaspur, Chhattisgarh - 495001
- **24/7 Helpline**: +91 98930 12345
- **Secondary Contact**: +91 94250 54321
- **Email**: `info@vaishnavitours.com` / `bookings@vaishnavitours.com`
