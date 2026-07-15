<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Peran Sesi | B-SMART</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8fafd;
            height: 100vh;
        }
        .peran-card {
            border: none;
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.04);
            background: #ffffff;
        }
        .btn-peran {
            border: 1px solid #e1e3e5;
            background: #ffffff;
            color: #3c4043;
            border-radius: 12px;
            transition: all 0.2s ease;
            text-align: left;
        }
        .btn-peran:hover {
            border-color: #1a73e8;
            background: rgba(26, 115, 232, 0.04);
            color: #1a73e8;
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">

<div class="container" style="max-width: 500px;">
    <div class="card peran-card p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="bg-primary text-white d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 60px; height: 60px; font-size: 24px;">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <h4 class="fw-bold text-dark mb-1">Selamat Datang, {{ Auth::user()->nama }}</h4>
            <p class="text-muted small">Akun Anda terdeteksi memiliki lebih dari satu peran otorisasi sistem. Silakan pilih salah satu untuk sesi ini:</p>
        </div>

        <div class="d-flex flex-column gap-3">
            @foreach($akses as $item)
                <form action="/set-peran" method="POST">
                    @csrf
                    <input type="hidden" name="role" value="{{ $item->role }}">
                    <input type="hidden" name="jabatan" value="{{ $item->jabatan }}">
                    <input type="hidden" name="kode_area" value="{{ $item->kode_area }}">
                    <input type="hidden" name="kode_project" value="{{ $item->kode_project }}">
                    
                    <button type="submit" class="btn btn-peran w-100 p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="fw-bold d-block" style="font-size: 14px;">{{ $item->jabatan }}</span>
                            <span class="text-muted d-block" style="font-size: 11px;">
                                Area/Unit Kerja: <strong class="text-primary">{{ $item->kode_area }}</strong>
                            </span>
                            @if($item->kode_project)
                                <span class="text-muted" style="font-size: 11px;">
                                    Project: <strong class="text-primary">{{ $item->kode_project }}</strong>
                                </span>
                            @else
                                <span class="text-muted" style="font-size: 11px;">
                                    Project: <strong class="text-primary">ALL</strong>
                                </span>
                            @endif
                        </div>
                        <i class="fa-solid fa-chevron-right text-black-50 fs-6"></i>
                    </button>
                </form>
            @endforeach
        </div>

        <div class="text-center mt-4 pt-2">
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-danger small fw-semibold text-decoration-none">
                <i class="fa-solid fa-power-off me-1"></i> Batal & Keluar Sistem
            </a>
            <form id="logout-form" action="/logout" method="POST" class="d-none">@csrf</form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>