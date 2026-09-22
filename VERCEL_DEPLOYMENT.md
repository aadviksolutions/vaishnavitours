# Vaishnavi Tours — Vercel Production Deployment Runbook

This document provides the complete, step-by-step production deployment guide for Vaishnavi Tours on **Vercel** with **PHP 8.3/FrankenPHP** and an **External MySQL Database**.

---

## Architecture Overview

```text
GitHub Repository
       │
       ▼ (Git Push)
Vercel Fluid Compute Platform
       │
       ▼
Container Image (Dockerfile.vercel / FrankenPHP + Caddy)
or Serverless Function (api/index.php + vercel.json)
       │
       ▼
External Managed MySQL Database (AWS RDS / PlanetScale / Aiven / Supabase)
```

> [!IMPORTANT]
> **Stateless Platform Rule**: Vercel functions and containers are stateless and ephemeral. The production database **must** be an external persistent MySQL-compatible database. **Never** use localhost, 127.0.0.1, or local SQLite files in production.

---

## 14-Step Deployment Checklist

### Step 1: Create a GitHub Repository
1. Log in to [GitHub](https://github.com/).
2. Create a new **Private** repository named `vaishnavi-tours` (or your company naming standard).
3. Do **not** initialize with a `.gitignore` or `README` (the project already contains both).

---

### Step 2: Push the Project to GitHub
1. In your local terminal, verify Git status and ensure no local credentials or temporary files are tracked:
   ```bash
   git status
   git diff --check
   ```
2. Set the remote origin and push the repository:
   ```bash
   git remote add origin https://github.com/YOUR_GITHUB_ORGANIZATION/vaishnavi-tours.git
   git branch -M main
   git push -u origin main
   ```

---

### Step 3: Set Up External MySQL Database
Provision a managed MySQL 8+ or MariaDB database on any cloud provider:
* **Recommended Managed Providers:**
  - **AWS RDS (MySQL 8.0+)**
  - **PlanetScale / Aiven for MySQL**
  - **DigitalOcean Managed Databases (MySQL)**
  - **Clever Cloud (MySQL)**
  - **Supabase (PostgreSQL/MySQL bridge)**

Make note of:
- `DB_HOST` (e.g., `mysql-prod.c12345.ap-south-1.rds.amazonaws.com`)
- `DB_PORT` (`3306`)
- `DB_DATABASE` (`vaishnavi_tours`)
- `DB_USERNAME` (e.g., `vt_admin`)
- `DB_PASSWORD` (strong generated password)
- Optional: `MYSQL_ATTR_SSL_CA` path or SSL certificate authority for secure connections.

---

### Step 4: Import Repository into Vercel
1. Log in to your [Vercel Dashboard](https://vercel.com/dashboard).
2. Click **Add New...** → **Project**.
3. Select your GitHub repository (`vaishnavi-tours`) and click **Import**.
4. **Framework Preset**: Leave as **Other** (Vercel automatically detects `Dockerfile.vercel` for container deployments).
5. **Root Directory**: `./` (project root).

---

### Step 5: Configure Production Environment Variables in Vercel
In the Vercel **Environment Variables** section, add the following variables for the **Production** environment:

| Variable | Recommended Production Value | Description |
| :--- | :--- | :--- |
| `APP_NAME` | `"Vaishnavi Tours"` | Brand application name |
| `APP_ENV` | `production` | Production environment mode |
| `APP_KEY` | `base64:...` | Laravel 32-byte encryption key (generate via `php artisan key:generate --show`) |
| `APP_DEBUG` | `false` | Disables verbose debug pages & stack traces |
| `APP_URL` | `https://vaishnavitours.vercel.app` | Production domain or custom domain |
| `DB_CONNECTION` | `mysql` | MySQL database driver |
| `DB_HOST` | `mysql-prod.your-host.com` | **External** host (NEVER localhost / 127.0.0.1) |
| `DB_PORT` | `3306` | MySQL port |
| `DB_DATABASE` | `vaishnavi_tours` | Production database name |
| `DB_USERNAME` | `vt_admin` | Database user |
| `DB_PASSWORD` | `YourStrongSecretPassword` | Database password |
| `SESSION_DRIVER` | `cookie` | Stateless encrypted client-side cookie session (or `database`) |
| `CACHE_STORE` | `database` | Persistent database cache store |
| `QUEUE_CONNECTION` | `database` | Persistent database queue connection |
| `FILESYSTEM_DISK` | `public` | Local public disk or `s3` for object storage |
| `LOG_CHANNEL` | `stderr` | Outputs logs directly to Vercel Runtime Logs |
| `MAIL_MAILER` | `smtp` | SMTP Mailer driver |
| `MAIL_HOST` | `smtp.mailgun.org` | SMTP host provider |
| `MAIL_PORT` | `587` | SMTP port |
| `MAIL_USERNAME` | `your_smtp_username` | SMTP user |
| `MAIL_PASSWORD` | `your_smtp_password` | SMTP password |
| `MAIL_ENCRYPTION`| `tls` | TLS Encryption |
| `MAIL_FROM_ADDRESS` | `bookings@vaishnavitours.com` | Sender address |
| `MAIL_FROM_NAME` | `"Vaishnavi Tours"` | Sender display name |
| `ADMIN_EMAIL` | `admin@vaishnavitours.com` | Initial admin account email |
| `ADMIN_PASSWORD` | `YourSecureAdminPass#2026` | Initial admin account password |

> [!CAUTION]
> **Never commit your `.env` file to Git.** Always add secrets exclusively through the Vercel Dashboard.

---

### Step 6: Deploy Application
1. In the Vercel dashboard, click **Deploy**.
2. Vercel triggers the multi-stage build:
   - Node 20 compiles frontend Vite assets into `public/build`.
   - Composer installs production PHP packages (`--no-dev --optimize-autoloader`).
   - FrankenPHP container image is built and stored in Vercel Container Registry (VCR).
3. Wait for the green **Ready** state.

---

### Step 7: Run Production Database Migrations & Seeds
Once your external database is reachable from Vercel:
1. Run migrations safely via the Vercel CLI, a deployment task, or a temporary secure artisan runner:
   ```bash
   php artisan migrate --force
   ```
   > [!WARNING]
   > **NEVER run `migrate:fresh` in production.** `migrate:fresh` drops all database tables and destroys production booking records! Always use `migrate --force`.

2. Seed baseline production configuration (Settings, Tariff Rates, Initial Admin):
   ```bash
   php artisan db:seed --class=ProductionSeeder --force
   ```
   > [!NOTE]
   > `ProductionSeeder` is idempotent (`firstOrCreate`) and only sets up essential company information and route tariffs. It does **not** insert demo customers, simulated bookings, or fake invoices.

---

### Step 8: Verify Application URL & SSL
1. Open your Vercel deployment URL (e.g., `https://vaishnavitours.vercel.app`).
2. Verify that:
   - HTTPS certificate is valid and active.
   - Homepage loads with zero console errors.
   - Vaishnavi Tours logo is clearly displayed in the header and footer.
   - Hero banner, fleet showcase, and tariff tables render with correct styling.

---

### Step 9: Verify Admin Login & Dashboard
1. Navigate to: `https://your-domain.vercel.app/admin` (or `/login`).
2. Enter the admin credentials configured in `ADMIN_EMAIL` and `ADMIN_PASSWORD`.
3. Verify access to:
   - `/admin/dashboard` (KPI stat cards, active trips, revenue, recent bookings)
   - `/admin/bookings`
   - `/admin/trips`
   - `/admin/vehicles`
   - `/admin/drivers`
   - `/admin/customers`
   - `/admin/payments`
   - `/admin/invoices`
   - `/admin/notifications`
   - `/admin/reports`
   - `/admin/settings`
4. Confirm non-admin users or unauthenticated visitors are blocked with HTTP 403 or redirected to login.

---

### Step 10: Verify Customer Registration & Portal
1. Open an incognito/private browser window.
2. Navigate to: `https://your-domain.vercel.app/register`.
3. Register a new test customer account with name, email, phone number, and password.
4. Verify immediate redirect to `/customer/dashboard` (or `/dashboard`).
5. Check that customer navigation tabs work:
   - `/bookings`
   - `/profile`
   - `/notifications`

---

### Step 11: Verify Public Booking Engine
1. Navigate to: `https://your-domain.vercel.app/booking`.
2. Fill out a booking inquiry:
   - Service Type: Outstation Round-Trip
   - Pickup Location: Mangal Chowk, Bilaspur
   - Destination: Raipur Airport
   - Travel Date & Time: Tomorrow 09:00 AM
   - Vehicle: Maruti Suzuki Dzire (or Ertiga)
   - Passenger details: Name, Mobile, Email
3. Submit the booking.
4. Confirm:
   - Redirection to `/booking-success/{booking}` with generated reference `VT-XXXX`.
   - The booking record is stored in the external database with status `Pending`.
   - Notification appears in the Admin Panel dashboard.

---

### Step 12: Verify Database Persistence & Transaction Safety
1. Log in to the Admin Panel.
2. Locate the booking created in Step 11.
3. Transition status from `Pending` → `Confirmed` → `Assign Vehicle` → `Assign Driver`.
4. Verify:
   - Overlap conflicts prevent double-booking the same car/driver.
   - Corresponding Trip and Invoice records are generated in the database.
   - Refresh the page and confirm state persists across serverless executions.

---

### Step 13: Verify Static Assets & Custom Error Pages
1. Test all brand assets:
   - Official Logo: `https://your-domain.vercel.app/assets/branding/vaishnavi-tours-logo.png`
   - Hero Image: `https://your-domain.vercel.app/assets/images/hero-taxi.jpg`
   - Ambulance/Emergency Image: `https://your-domain.vercel.app/assets/images/emergency-ambulance.jpg`
   - CSS & JavaScript bundles load from `public/build/assets/`.
2. Test custom error handling:
   - Visit an invalid route: `https://your-domain.vercel.app/non-existent-page-test`
   - Confirm branded **404 - Destination Not Found** error page renders without any stack traces or debug leaks.

---

### Step 14: Monitor Vercel Runtime Logs
1. Go to your Vercel Project Dashboard → **Logs**.
2. Filter by:
   - Status: `5xx` (should be zero)
   - Channel: `stderr`
3. Verify that database queries, route resolutions, and requests are running smoothly.

---

## Background Tasks, Cron & Queues on Vercel

### Laravel Scheduler (Cron)
On serverless platforms like Vercel, traditional long-running system daemons (`crond`) do not exist. To trigger scheduled tasks:
1. Define a Vercel Cron Job in `vercel.json`:
   ```json
   {
     "crons": [
       {
         "path": "/api/cron/scheduler",
         "schedule": "0 * * * *"
       }
     ]
   }
   ```
2. Protect the endpoint with a secret header: `CRON_SECRET=your-secret-token`.
3. Call `Artisan::call('schedule:run')` inside the handler.

### Queues & Asynchronous Jobs
* In production on Vercel, set:
  ```env
  QUEUE_CONNECTION=sync
  ```
  or use database queues (`QUEUE_CONNECTION=database`) processed via external worker services (e.g., AWS SQS, Laravel Cloud worker, or scheduled queue work runners).

---

## Summary of Completed Phase 4 Artifacts

- **`Dockerfile.vercel`**: Multi-stage OCI container build (Node 20 + Composer 2 + FrankenPHP/Caddy on PHP 8.3).
- **`Caddyfile`**: Production server config listening dynamically on `:{$PORT:80}`.
- **`vercel.json`**: Optimized routing, asset caching, security headers, and function timeouts.
- **`api/index.php`**: Clean serverless function entrypoint with strict `APP_KEY` validation and no SQLite fallback.
- **`bootstrap/app.php`**: Serverless `/tmp/storage` configuration.
- **`.env.example`**: Complete production placeholders for MySQL, Mail, S3, and Payment Gateways.
- **`.gitignore`**: Strictly ignores `.env` and `.env.*` while keeping `.env.example`.
- **`database/seeders/ProductionSeeder.php`**: Safe seeders for production (Settings, Rates, Baseline Admin).
- **`database/seeders/DemoSeeder.php`**: Isolated development seeders.
- **`database/seeders/DatabaseSeeder.php`**: Production environment protection.
- **Branded Error Pages**: `404`, `403`, `419`, `429`, `500` error views matching Vaishnavi Tours design.
