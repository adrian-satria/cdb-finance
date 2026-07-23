# 06 — Installation Guide (Local Development)

## 6.1 Prasyarat

- PHP 8.2+
- Composer 2.x
- Node.js 18+ & npm
- MySQL 8.0+ (atau MariaDB 10.6+)
- Laragon (Windows) / Valet (macOS) / Docker

## 6.2 Setup Laragon (Windows)

1. Download & install Laragon from https://laragon.org
2. Pastikan PHP version ≥ 8.2.0 (Laragon → Menu → PHP → Version)
3. Start Laragon (Apache + MySQL)

## 6.3 Clone & Install

```bash
# 1. Clone repositori
git clone <repo-url> cdb-finance
cd cdb-finance

# 2. Install PHP dependencies
composer install

# 3. Install frontend dependencies
npm install

# 4. Copy environment
cp .env.example .env
```

## 6.4 Konfigurasi .env

Edit `.env`:

```dotenv
APP_NAME="CDB Finance - B-SMART"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_cdb_finance
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
SESSION_ENCRYPT=false

LOG_LEVEL=debug
```

## 6.5 Generate Key & Database

```bash
# Generate application key
php artisan key:generate

# Buat database via Laragon:
#   Laragon → Database → Open → phpMyAdmin
#   Atau via MySQL CLI:
mysql -u root -e "CREATE DATABASE IF NOT EXISTS db_cdb_finance"
```

## 6.6 Migrate & Seed

```bash
# Run migration
php artisan migrate

# Seed data awal
php artisan db:seed

# Seed admin user (akan generate random password, catat!)
php artisan db:seed --class=AdminUserSeeder
```

Default admin akan dibuat dengan:
- Username: `admin_keuangan`
- Password: **(random, lihat output terminal)**

## 6.7 Storage & Build

```bash
# Storage link (untuk file upload)
php artisan storage:link

# Build frontend assets (dev)
npm run dev

# Atau build untuk production
npm run build
```

## 6.8 Jalankan

```bash
# Terminal 1: Laravel development server
php artisan serve

# Terminal 2 (opsional): Vite dev server
npm run dev
```

Buka `http://localhost:8000` di browser.

## 6.9 Troubleshooting Local Setup

| Masalah | Solusi |
|---------|--------|
| `No application key` | `php artisan key:generate` |
| `Target class ... does not exist` | `composer dump-autoload` |
| Blank page / 500 | Cek `storage/logs/laravel.log` |
| Vite asset 404 | `npm install && npm run dev` di terminal terpisah |
| SQLSTATE[HY000] [2002] | Pastikan MySQL running di Laragon |

## 6.10 Membuat User Baru (Testing)

Akses `http://localhost:8000/admin/user` setelah login sebagai ADMIN.
Atau via tinker:
```bash
php artisan tinker
> $user = \App\Models\User::create([
    'nama' => 'Test User',
    'username' => 'testuser',
    'password' => bcrypt('Test1234'),
  ]);
> $user->akses()->create([
    'role' => 'MAKER',
    'jabatan' => 'Staff',
    'kode_area' => 'PUSAT',
  ]);
```
