# 11 — Maintenance Guide

## 11.1 Backup Database

### Via SSH
```bash
# Backup seluruh database
mysqldump -u USERNAME -p DATABASE_NAME > backup_$(date +%Y%m%d_%H%M%S).sql

# Backup tanpa data (structure only)
mysqldump -u USERNAME -p --no-data DATABASE_NAME > schema_backup.sql
```

### Via phpMyAdmin hPanel
1. Buka phpMyAdmin
2. Pilih database
3. Tab **Export**
4. Pilih **SQL** → **Go**
5. Simpan file

### Rekomendasi Tools
- **spatie/laravel-backup** — backup otomatis database + files ke cloud
- Setup cron harian untuk backup

## 11.2 Storage Maintenance

### Log Rotation
File `storage/logs/laravel.log` bisa membesar seiring waktu.

### Bersihkan Log
```bash
# Via SSH
truncate -s 0 /home/.../cdb_finance/storage/logs/laravel.log

# Atau hapus file (akan dibuat ulang otomatis)
rm /home/.../cdb_finance/storage/logs/laravel.log
```

### Bersihkan Compiled Views
```bash
php artisan view:clear
```

## 11.3 Cache Management

```bash
# Clear all cache
php artisan optimize:clear

# Production cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

Cache di production harus di-refresh setiap kali ada perubahan:
- `.env` → `config:cache`
- `routes/*` → `route:cache`
- Blade views → `view:cache`

## 11.4 Queue Worker

Notifikasi dikirim via queue (database driver).

### Cek Status Worker
```bash
ps aux | grep queue:work
```

### Start Worker (nohup)
```bash
cd /home/.../cdb_finance
nohup php artisan queue:work --sleep=3 --tries=3 --timeout=60 &
```

### Restart Worker (setelah deploy)
```bash
php artisan queue:restart
```

### Supervisor Config (Production)
Buat file `/etc/supervisor/conf.d/cdb-finance-queue.conf`:
```ini
[program:cdb-finance-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /home/.../cdb_finance/artisan queue:work --sleep=3 --tries=3 --timeout=60
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=u813128606
numprocs=2
redirect_stderr=true
stdout_logfile=/home/.../cdb_finance/storage/logs/queue-worker.log
```

## 11.5 Scheduler (Cron)

Cron job harus terdaftar di hPanel:
```text
* * * * * /opt/alt/php82/usr/bin/php /home/.../cdb_finance/artisan schedule:run
```

Untuk menambah task terjadwal, edit `app/Console/Kernel.php`.

## 11.6 Troubleshooting

### 500 Error
```bash
# 1. Cek log
tail -100 /home/.../cdb_finance/storage/logs/laravel.log

# 2. Cek permission
ls -la /home/.../cdb_finance/storage/
chmod -R 775 storage bootstrap/cache

# 3. Cek APP_KEY
grep APP_KEY /home/.../cdb_finance/.env
# Harus berisi "base64:xxx..." — jika kosong: php artisan key:generate

# 4. Cek PHP version
php -v
# Harus 8.2.x
```

### 419 Page Expired
- CSRF token tidak match
- Penyebab: session expired, atau session tidak bisa write
- Solusi: pastikan `storage/framework/sessions/` writable
- Atau set `SESSION_DRIVER=file`

### 403 Forbidden
- Tidak punya akses role
- Cek `user_access` di database
- Cek session role: apakah masih valid?

### 404 Not Found
- Route tidak terdaftar
- Atau Document Root salah (harus ke folder `/public`)

### Login Gagal
| Penyebab | Solusi |
|----------|--------|
| Password salah | Reset via phpMyAdmin: `UPDATE users SET password=bcrypt('NewPass123') WHERE username='xxx'` |
| User tidak punya role | Tambahkan `user_access` |
| Session corrupt | Clear browser cookies + cache |
| Rate limit | Tunggu 1 menit |

### PDF Tidak Muncul
- Cek `php -m | grep dom` — pastikan `dom` extension aktif
- Cek `storage/logs/laravel.log` untuk error DomPDF
- Pastikan font family di template ada di server

## 11.7 Update Migration

```bash
# Cek status migration
php artisan migrate:status

# Jalankan migration baru
php artisan migrate --force

# Rollback (jika error)
php artisan migrate:rollback --step=1 --force
```

## 11.8 Performance Monitoring

### Cek Query Lambat
```sql
-- Via phpMyAdmin
SHOW FULL PROCESSLIST;
SELECT * FROM mysql.slow_log ORDER BY start_time DESC LIMIT 10;
```

### Cek Disk Usage
```bash
df -h
du -sh /home/.../cdb_finance/storage/logs/
du -sh /home/.../cdb_finance/storage/framework/views/
```

### Rekomendasi
- Gunakan **Redis** untuk cache & session jika traffic tinggi
- Tambahkan **index** di kolom yang sering di-query (`no_surat`, `kode_project`, `kode_area`, `status_surat`)
- Monitor `storage/framework/views/` — compiled views bisa membesar

## 11.9 Security Monitoring

Secara rutin:
1. Cek `audit_trails` untuk aktivitas mencurigakan
2. Cek `storage/logs/laravel.log` untuk error berulang
3. Pastikan `storage/framework/sessions/` tidak membesar tidak wajar
4. Ganti password admin secara berkala
5. Backup database sebelum update besar
