<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | B-SMART Finance - CDB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #06b6d4 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Animated Background Shapes */
        .background-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 20s infinite ease-in-out;
        }

        .shape:nth-child(1) {
            width: 300px;
            height: 300px;
            top: -150px;
            left: -150px;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 200px;
            height: 200px;
            bottom: -100px;
            right: -100px;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            width: 150px;
            height: 150px;
            top: 50%;
            left: 10%;
            animation-delay: 4s;
        }

        .shape:nth-child(4) {
            width: 250px;
            height: 250px;
            top: 20%;
            right: 15%;
            animation-delay: 1s;
        }

        @keyframes float {
            0%, 100% {
                transform: translate(0, 0) scale(1);
            }
            25% {
                transform: translate(30px, -30px) scale(1.1);
            }
            50% {
                transform: translate(-20px, 20px) scale(0.9);
            }
            75% {
                transform: translate(20px, 30px) scale(1.05);
            }
        }

        /* Main Container */
        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 450px;
            margin: 0 auto;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Header Section with Logo */
        .login-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #06b6d4 100%);
            padding: 30px 30px 25px 30px;
            text-align: center;
            color: white;
            position: relative;
        }

        .login-header::before {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -50px;
            right: -50px;
            animation: pulse 3s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 0.5;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.3;
            }
        }

        .brand-logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            animation: logoFloat 3s infinite ease-in-out;
            position: relative;
            z-index: 2;
            padding: 8px;
            overflow: hidden;
        }

        .brand-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        @keyframes logoFloat {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        .brand-title {
            font-size: 14px;
            font-weight: 500;
            opacity: 0.95;
            position: relative;
            z-index: 2;
            color: white;
            text-shadow: 0 1px 5px rgba(0, 0, 0, 0.15);
        }

        /* Form Section */
        .login-body {
            padding: 35px 30px;
        }

        .welcome-text {
            text-align: center;
            margin-bottom: 25px;
        }

        .welcome-text h2 {
            font-size: 22px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 6px;
        }

        .welcome-text p {
            font-size: 13px;
            color: #6b7280;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 18px;
            transition: color 0.3s;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px 14px 48px;
            font-size: 15px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            transition: all 0.3s ease;
            background: #f9fafb;
        }

        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .form-control:focus + .input-icon {
            color: #3b82f6;
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #9ca3af;
            font-size: 18px;
            transition: color 0.3s;
            z-index: 10;
        }

        .password-toggle:hover {
            color: #3b82f6;
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            font-size: 16px;
            font-weight: 600;
            color: white;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #06b6d4 100%);
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(59, 130, 246, 0.5);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert {
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 24px;
            border: none;
            animation: shake 0.5s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .footer-text {
            text-align: center;
            margin-top: 20px;
            color: #6b7280;
            font-size: 12px;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .login-container {
                padding: 10px;
            }

            .login-header {
                padding: 25px 20px 20px 20px;
            }

            .brand-logo {
                width: 70px;
                height: 70px;
            }

            .brand-title {
                font-size: 13px;
            }

            .login-body {
                padding: 30px 20px;
            }

            .welcome-text h2 {
                font-size: 20px;
            }
        }
    </style>
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
    <script>
        // Password Toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('passwordInput');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        // Form Animation on Submit
        const loginForm = document.getElementById('loginForm');
        loginForm.addEventListener('submit', function(e) {
            const btn = this.querySelector('.btn-login');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
            btn.style.pointerEvents = 'none';
        });

        // Input Focus Animation
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>
