# 10 — Security

## 10.1 Security Headers

Semua response HTTP dilengkapi header keamanan via `SecurityHeaders` middleware:

| Header | Value | Fungsi |
|--------|-------|--------|
| `X-Frame-Options` | `SAMEORIGIN` | Cegah clickjacking, izinkan iframe dari domain sendiri |
| `X-Content-Type-Options` | `nosniff` | Cegah MIME sniffing |
| `X-XSS-Protection` | `1; mode=block` | Proteksi XSS (legacy browser) |
| `Referrer-Policy` | `strict-origin-when-cross-origin` | Batasi referrer |
| `Permissions-Policy` | `camera=(), microphone=(), geolocation=()` | Nonaktifkan API sensitif |
| `Strict-Transport-Security` | `max-age=31536000; includeSubDomains` | Paksa HTTPS (hanya jika koneksi HTTPS) |
| `Content-Security-Policy` | `default-src 'self'` + whitelist CDN | Batasi sumber konten |

## 10.2 CSP Detail

```
default-src 'self';
script-src   'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com
             https://kit.fontawesome.com 'unsafe-inline';
style-src    'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com
             https://fonts.googleapis.com 'unsafe-inline';
font-src     'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com
             https://fonts.gstatic.com data:;
img-src      'self' data:;
connect-src  'self' https://cdnjs.cloudflare.com;
frame-src    'self';
```

## 10.3 Autentikasi

| Mekanisme | Implementasi |
|-----------|--------------|
| Session | Database/File driver, 120 menit lifetime |
| Password | Bcrypt (12 rounds) |
| Rate Limit | 5 percobaan/menit (login), 30/menit (operasi) |
| Inactivity Timeout | 7200 detik, logout paksa |
| CSRF | Token per session, semua POST |

## 10.4 Otorisasi

3 lapis kontrol akses:
1. **Middleware `auth`** — pastikan user login
2. **Middleware `validate.session`** — validasi session + role masih valid di DB
3. **Middleware `role:ADMIN`** — whitelist role untuk route admin
4. **Controller scope** — filter query berdasarkan area/project

### Privilege Escalation Detection

`ValidateSession` membandingkan session role dengan database setiap request. Jika tidak cocok:
- Log `PRIVILEGE_ESCALATION_ATTEMPT` ke audit trail
- Logout paksa

### Illegal Access Logging

`CheckRole` mencatat setiap percobaan akses endpoint terlarang:
- Log `ILLEGAL_PRIVILEGE_ACCESS` ke audit trail
- Return 403

## 10.5 XSS Prevention

| Lapisan | Metode |
|---------|--------|
| Blade templating | `{{ }}` auto-escape (kecuali `{!! !!}` yang dihindari) |
| Inline event handlers | Dihapus, diganti `data-*` attributes + JS event delegation |
| CSP | Batasi sumber script/style |

## 10.6 File Upload Security

| Check | Detail |
|-------|--------|
| MIME validation | `finfo()` real content check |
| Extension | Whitelist: pdf, jpg, jpeg, png |
| Size limit | 5MB per file, max 5 file |
| Path traversal | `basename()` sanitasi |
| Storage | Di luar `public/` (`storage/app/private/lampiran_spp/`) |
| Download | Authorisasi via `canAccessSpp()` sebelum download |

## 10.7 SQL Injection

- Semua query menggunakan **Eloquent ORM** atau **parameterized queries** (`DB::table()->where()`)
- Tidak ada raw SQL concatenation
- Order by column: whitelist `in_array()`

## 10.8 Session Configuration (Production)

```dotenv
SESSION_DRIVER=file
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_LIFETIME=120
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

## 10.9 Database Security

- User dedicated (bukan root)
- Password kuat (min 16 karakter)
- Hanya akses dari localhost
- Strict mode aktif

## 10.10 Audit Trail Coverage

| Aksi | Log |
|------|-----|
| Login | Via Laravel default |
| Logout | Via Laravel default |
| Buat SPP | INSERT_SPP |
| Approve SPP | APPROVE_AREA / APPROVE_PROJECT / APPROVE_FINANCE |
| Tolak SPP | REJECT |
| Revisi SPP | REVISE |
| Cairkan dana | DISBURSED_FINAL |
| Cetak PDF | CETAK_PDF_SECURE |
| Ganti password | CHANGE_PASSWORD |
| Switch role | SWITCH_ROLE |
| Gagal otorisasi | ILLEGAL_PRIVILEGE_ACCESS |
| Akses ilegal file | ILLEGAL_FILE_ACCESS |
| Fraud attempt | FRAUD_ATTEMPT |
| Session timeout | SESSION_TIMEOUT |

## 10.11 Rate Limiting

| Endpoint | Limit |
|----------|-------|
| POST /login | 5 per menit |
| POST /spp/simpan | 30 per menit |
| POST /spp/validasi | 30 per menit |
| POST /spp/cairkan | 10 per menit |
| POST admin/user/store | 10 per menit |
| POST admin/budget/import | 10 per menit |
| POST admin/settings | 30 per menit |
