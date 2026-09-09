<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemeliharaan Sistem | Finance Management Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #06b6d4 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .error-card { text-align: center; max-width: 480px; padding: 40px; background: rgba(255,255,255,0.98); border-radius: 24px; box-shadow: 0 30px 80px rgba(0,0,0,0.3); }
        .icon { font-size: 64px; color: #2563eb; margin-bottom: 16px; }
        .error-title { font-size: 22px; font-weight: 800; color: #1a1a1a; margin-bottom: 8px; }
        .error-desc { font-size: 14px; color: #6b7280; margin-bottom: 24px; }
        .brand { font-size: 12px; color: #9ca3af; margin-top: 24px; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="icon"><i class="fa-solid fa-wrench"></i></div>
        <div class="error-title">Sistem Sedang dalam Pemeliharaan</div>
        <div class="error-desc">Kami sedang melakukan pembaruan sistem. Silakan kembali lagi dalam beberapa saat. Terima kasih atas kesabaran Anda.</div>
        <div class="brand">&copy; {{ date('Y') }} Finance Management Demo</div>
    </div>
</body>
</html>


