<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | B-SMART Finance - CDB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/login.css'])
</head>
<body>
    <!-- Animated Background -->
    <div class="background-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <!-- Login Container -->
    <div class="login-container">
        <div class="login-card">
            <!-- Header Section with Logo -->
            <div class="login-header">
                <div class="brand-logo">
                    <img src="{{ asset('images/Logo CD Bethesda.png') }}" alt="Logo CD Bethesda YAKKUM">
                </div>
                <div class="brand-title">Finance Management System</div>
            </div>

            <!-- Form Section -->
            <div class="login-body">
                <div class="welcome-text">
                    <h2>Selamat Datang Kembali</h2>
                    <p>Silakan login untuk melanjutkan ke dashboard</p>
                </div>

                @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    </div>
                @endif

                <form action="/login" method="POST" id="loginForm">
                    @csrf
                    
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input 
                                type="text" 
                                name="username" 
                                class="form-control" 
                                placeholder="Masukkan username Anda"
                                required 
                                autofocus
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input 
                                type="password" 
                                name="password" 
                                class="form-control" 
                                id="passwordInput"
                                placeholder="Masukkan password Anda"
                                required
                            >
                            <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                        </div>
                    </div>

                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Login Sekarang
                    </button>
                </form>

                <div class="footer-text">
                    <p>&copy; {{ date('Y') }} CDB Finance. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @vite(['resources/js/app.js'])
</body>
</html>
