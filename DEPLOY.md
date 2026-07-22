# Panduan Deploy CDB Finance ke hPanel (Niagahoster)

## Prasyarat Hosting

- PHP 8.2+ (cek/setting di hPanel → Advanced → PHP Selector)
- MySQL (buat via hPanel → MySQL Databases)
- Domain sudah指向 hosting
- SSH (aktifasi di hPanel → Advanced → SSH)

---

## Langkah Deploy

### 1. Build Asset di Laptop
```bash
# Terminal di laptop (folder project)
npm install
npm run build
```

### 2. ZIP Project
Yang wajib di-zip:
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

### 3. Upload ke hPanel

2 cara:

**Cara A (Rekomendasi) — via hPanel File Manager:**
1. Login hPanel → File Manager → masuk ke folder `public_html/`
2. Upload ZIP → klik kanan → Extract
3. Pindahkan semua isi file ke `public_html/`
4. Set Document Root: hPanel → Advanced → Document Root → Pilih domain → `/public`

**Cara B — via SSH:**
```bash
# Upload via SCP/FileZilla ke folder home, lalu di server:
cd /home/site-name/public_html
unzip project.zip -d .
mv public_html/* .   # jika zip berisi folder public_html/
```

### 4. Setup .env
1. Di File Manager, rename `.env.example` jadi `.env`
2. Edit `.env`, isi sesuai hosting:
```
APP_URL=https://domainkamu.com
DB_DATABASE=nama_database
DB_USERNAME=user_database
DB_PASSWORD=password_database
MAIL_HOST=smtp.hostinger.com
MAIL_USERNAME=email@domainkamu.com
MAIL_PASSWORD=password_email
```

### 5. Jalankan Deploy Script

Via hPanel SSH (atau Terminal di File Manager):
```bash
cd /home/site-name/public_html
bash deploy.sh
```

### 6. Selesai
- Buka `https://domainkamu.com/login`
- Login dengan user admin (password muncul saat seeding)
- Ganti password admin segera

---

## Setelah Deploy — Wajib

### Cron Job (Schedule)
Di hPanel → Advanced → Cron Job → tambah:
```
* * * * * /usr/bin/php /home/site-name/public_html/artisan schedule:run >> /dev/null 2>&1
```

### Queue Worker
Via SSH, jalankan:
```bash
cd /home/site-name/public_html
nohup php artisan queue:work --sleep=3 --tries=3 &
```

### Keamanan
1. Hapus `deploy.sh` dari server setelah selesai
2. Ganti password default admin
3. Jika sudah pakai HTTPS, edit `.env`: `SESSION_SECURE_COOKIE=true`

---

## Troubleshooting

| Problem | Solusi |
|---------|--------|
| 500 error | Cek `storage/logs/laravel.log` |
| 419 page expired | Set `SESSION_DRIVER=file`, pastikan `storage/framework/sessions/` writable |
| File tidak bisa upload | `chmod -R 775 storage/` |
| CSS/JS 404 | Pastikan `npm run build` sudah dijalankan + folder `public/build/` ada |
| Halaman putih | Cek `APP_DEBUG=true` dulu di .env, lalu refresh |
| Migration error | Pastikan MySQL user punya ALL PRIVILEGES di database |
| Composer error | Jalankan `composer install --no-dev` via SSH |
