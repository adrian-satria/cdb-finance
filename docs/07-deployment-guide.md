# 07 — Deployment Guide (hPanel Niagahoster)

## 7.1 Prasyarat Hosting

| Kebutuhan | Minimal |
|-----------|---------|
| PHP | 8.2 (via PHP Selector di hPanel) |
| MySQL | 5.7+ / MariaDB 10.3+ |
| Web Server | Apache / Nginx |
| SSH | Aktifasi di hPanel → Advanced → SSH |
| Domain | Sudah pointing ke hosting |

## 7.2 Build Assets (di Laptop)

```bash
cd cdb-finance
npm install
npm run build
```

## 7.3 ZIP Project

Folder di-zip:
```
app/          bootstrap/    config/
database/     public/       resources/
routes/       storage/      vendor/
.env.example  artisan       composer.json
composer.lock deploy.sh
```

Tidak perlu:
```
node_modules/   .git/   tests/
storage/logs/*.log
```

## 7.4 Upload & Extract

Via **hPanel File Manager**:
1. Buka `/home/u813128606/domains/cdbethesda.org/public_html/cdb_finance/`
2. Upload ZIP → klik kanan → Extract
3. Set **Document Root**: hPanel → Advanced → Document Root → domain `finance.cdbethesda.org` → `/home/.../cdb_finance/public`

## 7.5 Setup .env

Via File Manager, rename `.env.example` → `.env`, isi:

```dotenv
APP_NAME="CDB Finance - B-SMART"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://finance.cdbethesda.org

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=nama_database_dari_hpanel
DB_USERNAME=user_database_dari_hpanel
DB_PASSWORD=password_database_dari_hpanel

SESSION_DRIVER=file
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true

LOG_LEVEL=error
CACHE_STORE=file
```

## 7.6 Deploy Script

Via **SSH** terminal:

```bash
cd /home/u813128606/domains/cdbethesda.org/public_html/cdb_finance
bash deploy.sh
```

Jika error PHP version, gunakan alias:
```bash
alias php='/opt/alt/php82/usr/bin/php'
alias composer='/opt/alt/php82/usr/bin/php /opt/alt/composer2/usr/bin/composer'
bash deploy.sh
```

Script `deploy.sh` akan otomatis:
1. `composer install --no-dev`
2. `php artisan key:generate`
3. `php artisan migrate --force`
4. `php artisan db:seed --class=AdminUserSeeder --force`
5. `php artisan storage:link --force`
6. Optimasi cache
7. Set permission

## 7.7 Cron Job

Di hPanel → Advanced → Cron Job:

```
* * * * * /opt/alt/php82/usr/bin/php /home/u813128606/domains/cdbethesda.org/public_html/cdb_finance/artisan schedule:run
```

Gunakan tipe **Kustom**, bukan PHP.

## 7.8 Queue Worker

Untuk notifikasi, jalankan via SSH:

```bash
cd /home/u813128606/domains/cdbethesda.org/public_html/cdb_finance
nohup /opt/alt/php82/usr/bin/php artisan queue:work --sleep=3 --tries=3 &
```

## 7.9 Post-Deploy Checklist

| Item | Status |
|------|--------|
| Domain bisa diakses via HTTPS | ✅/❌ |
| Login page muncul | ✅/❌ |
| Login dengan admin_keuangan | ✅/❌ |
| Ganti password admin | ✅/❌ |
| Buat user + role access | ✅/❌ |
| Buat SPP & test workflow | ✅/❌ |
| Test cetak PDF | ✅/❌ |
| Cron job terdaftar | ✅/❌ |
| Storage writable | ✅/❌ |
| Hapus deploy.sh | ✅/❌ |

## 7.10 Troubleshooting

| Error | Penyebab | Solusi |
|-------|----------|--------|
| 500 error | Storage permission | `chmod -R 775 storage bootstrap/cache` |
| 419 expired | Session config | Set `SESSION_DRIVER=file` |
| SQLSTATE[1045] | DB credentials salah | Cek `.env` DB_USERNAME/PASSWORD |
| Vite asset 404 | Belum build | `npm run build` di laptop, upload `public/build/` |
| Composer error | PHP version | Pakai `/opt/alt/php82/usr/bin/php` |
| No application key | APP_KEY kosong | `php artisan key:generate` |
| Login page not found | Document root salah | Set ke folder `/public` |
