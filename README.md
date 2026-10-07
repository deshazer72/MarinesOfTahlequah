# Marines of Tahlequah

Community website for the Marines of Tahlequah (MOT), dedicated advocates for service members, veterans, and their families in Tahlequah, Oklahoma.

Built with **Laravel**, **Livewire**, **Flux UI**, **Tailwind CSS v4**, and **PostgreSQL**. Hosted on **Laravel Cloud**.

---

## ☁️ Laravel Cloud Hosting & Infrastructure

Both **Rebirth** and **Marines of Tahlequah** are hosted on [Laravel Cloud](https://cloud.laravel.com/).

### Why SQLite Was Deleting Users on Laravel Cloud
Laravel Cloud uses stateless, immutable application containers. Every git push triggers an automated build and deploy:
1. A fresh container is spun up with the repository code.
2. If SQLite is used, the `.sqlite` file resides on the container's ephemeral disk.
3. When new users registered, their records were written to the ephemeral container. The moment a new deployment occurred or the container recycled, the container disk was destroyed and recreated from Git, wiping all newly added users.

### The Solution: Managed PostgreSQL on Laravel Cloud
1. **Provision PostgreSQL:** In your Laravel Cloud dashboard for the `MarinesOfTahlequah` project/environment, click **Databases** > **Create Database** and select **PostgreSQL**.
2. **Attach Database:** Attach the PostgreSQL database to the environment. Laravel Cloud automatically injects all necessary connection credentials (`DB_CONNECTION=pgsql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `DATABASE_URL`).
3. **Deployment Hook:** Ensure your deployment script in Laravel Cloud includes:
   ```bash
   php artisan migrate --force
   ```
4. **Initial Seed (Run Once):** In the Laravel Cloud terminal/console for your environment, run:
   ```bash
   php artisan db:seed --force
   ```
   *(Or run `php database/migrate-sqlite-to-pgsql.php` if you want to import your existing SQLite users and data)*

---

## 🛠️ Tech Stack & Architecture

- **Backend:** Laravel 12 / 13 with PHP 8.2+
- **Frontend / Components:** Livewire Single-File Components (SFC) + Flux UI
- **Styling:** Tailwind CSS v4 (`@tailwindcss/vite`, USMC Scarlet & Gold `@theme`)
- **Database:** Managed PostgreSQL on Laravel Cloud (or local PostgreSQL)
- **Asset Bundler:** Vite

---

## 🚀 Local Development Setup

### 1. Prerequisites
- PHP 8.2+
- Composer
- Node.js 20+ & npm
- PostgreSQL (via Docker or local service)

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Set your database credentials in `.env`:
```ini
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=marines_of_tahlequah
DB_USERNAME=postgres
DB_PASSWORD=
```

### 4. Local PostgreSQL with Docker (Optional)
```bash
docker compose up -d
```

### 5. Run Migrations & Seeders
```bash
php artisan migrate --seed
```

#### Seeded Accounts:
- **Primary Super Admin:** `tiger72.jd@gmail.com` (password: `password`)
- **Super Admin:** `superadmin@marinesoftahlequah.com` (password: `password`)
- **Admin:** `admin@marinesoftahlequah.com` (password: `password`)
- **Member:** `user@marinesoftahlequah.com` (password: `password`)

### 6. Start Development Server
```bash
composer run dev
```

---

## 📌 Roles & Access Control

- **Super Admin:** Full access to all events, about content, and user role management (`/users`). Can assign roles (`user`, `admin`, `superadmin`) and manage member accounts. Protected from self-deletion or accidental deletion of the last super admin.
- **Admin:** Can create, edit, and delete events (`/events/create`, `/events/{id}/edit`) and about content (`/about/create`, `/about/{id}/edit`).
- **User / Member:** Can view events, read about articles, donate via PayPal/Cash App, update profile/password, and view their dashboard.
- **Guest / Public:** Can view homepage slideshow, about sections, events calendar, event detail pages, and donation QR codes.
