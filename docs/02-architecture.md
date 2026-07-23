# 02 — Architecture

## 2.1 Request Lifecycle

```
Browser
  │
  ▼
public/index.php
  │
  ▼
bootstrap/app.php
  │
  ▼
HTTP Kernel
  ├─ TrustProxies
  ├─ HandleCors
  ├─ PreventRequestsDuringMaintenance
  ├─ ValidatePostSize
  ├─ TrimStrings
  ├─ ConvertEmptyStringsToNull
  ├─ EncryptCookies
  ├─ AddQueuedCookiesToResponse
  ├─ StartSession
  ├─ ShareErrorsFromSession
  ├─ VerifyCsrfToken
  ├─ SubstituteBindings
  ├─ SecurityHeaders          ← Custom: CSP, HSTS, XFO, dll
  │
  ▼
Router
  ├─ auth middleware           ← Laravel built-in
  ├─ validate.session          ← Custom: validasi session + inactivity timeout
  └─ role:ADMIN                ← Custom: check role whitelist
       │
       ▼
Controller
  ├─ FormRequest (validasi)
  ├─ Service Layer (business logic)
  └─ Model / DB (data)
       │
       ▼
Blade View
```

## 2.2 Middleware Stack

### Global Middleware (web group)

| Middleware | Fungsi |
|-----------|--------|
| `EncryptCookies` | Enkripsi cookie |
| `AddQueuedCookiesToResponse` | Queue cookies |
| `StartSession` | Inisialisasi session |
| `ShareErrorsFromSession` | Share flash data ke view |
| `VerifyCsrfToken` | Proteksi CSRF semua POST |
| `SubstituteBindings` | Route model binding |
| `SecurityHeaders` | Header keamanan HTTP |

### Route Middleware

| Middleware | Route | Fungsi |
|-----------|-------|--------|
| `auth` | All protected routes | Wajib login |
| `validate.session` | All core routes | Validasi session + role + inactivity (2 jam) |
| `role:ADMIN` | `/admin/*` | Hanya role ADMIN |
| `throttle:5,1` | `POST /login` | Rate limit login (5x/menit) |
| `throttle:30,1` | `POST /spp/simpan`, `/spp/validasi` | Rate limit operasi (30x/menit) |
| `throttle:10,1` | `POST /spp/cairkan`, `/admin/budget/import` | Rate limit ketat (10x/menit) |

## 2.3 Service Layer

Semua business logic dipisahkan ke service classes:

| Service | Tanggung Jawab |
|---------|----------------|
| `SppWorkflowService` | State machine, validasi transisi, hitung next position |
| `BudgetValidationService` | Validasi pagu, pessimistic locking, bcmath precision |
| `SppNumberGeneratorService` | Generate nomor SPP, sequence, format |
| `FileUploadService` | Upload lampiran + tanda tangan, MIME validation |
| `NotificationService` | Kirim notifikasi (batch insert), sendToRole, sendToUser |
| `AuditLogService` | Catat log audit ke tabel audit_trails |

### Dependency Injection

```php
class SppController extends Controller
{
    public function __construct(
        protected SppWorkflowService $workflowService,
        protected BudgetValidationService $budgetService,
        protected SppNumberGeneratorService $numberService,
        protected FileUploadService $fileService,
        protected NotificationService $notificationService
    ) {}
}
```

## 2.4 Observer Pattern

`SppObserver` menangani event lifecycle SPP:

| Event | Aksi |
|-------|------|
| `created` | Catat ke `spp_history` + `audit_trails` |
| `updated` | Catat perubahan ke `spp_history` + `audit_trails` (payload_before/after JSON) |

Registrasi di `AppServiceProvider::boot()`:
```php
SuratPermintaan::observe(SppObserver::class);
```

## 2.5 Session Architecture

```
Session data stored:
  ├─ 'role'           → Role aktif user
  ├─ 'jabatan'        → Nama jabatan organisasi
  ├─ 'kode_area'      → Unit kerja area
  ├─ 'kode_project'   → Project scope
  ├─ 'user_roles'     → Semua role yang dimiliki user (array)
  └─ 'last_activity'  → Timestamp untuk inactivity timeout
```

- Inactivity timeout: 7200 detik (2 jam)
- Session driver: `file` (production), `database` (development)
- Session encryption: `true` (production), `false` (development)

## 2.6 Security Headers (per response)

| Header | Value |
|--------|-------|
| `X-Frame-Options` | `SAMEORIGIN` |
| `X-Content-Type-Options` | `nosniff` |
| `X-XSS-Protection` | `1; mode=block` |
| `Referrer-Policy` | `strict-origin-when-cross-origin` |
| `Permissions-Policy` | `camera=(), microphone=(), geolocation=()` |
| `Strict-Transport-Security` | `max-age=31536000; includeSubDomains` (HTTPS only) |
| `Content-Security-Policy` | `default-src 'self'` + whitelist CDN |
