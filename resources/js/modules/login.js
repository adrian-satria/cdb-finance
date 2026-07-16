export function initLoginForm() {
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('passwordInput');
    const loginForm = document.getElementById('loginForm');

    // Password toggle
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            togglePassword.classList.toggle('fa-eye');
            togglePassword.classList.toggle('fa-eye-slash');
        });
    }

    // Form submit animation
    if (loginForm) {
        loginForm.addEventListener('submit', () => {
            const btn = loginForm.querySelector('.btn-login');
            if (btn) {
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
                btn.style.pointerEvents = 'none';
            }
        });
    }

    // Focus animation
    document.querySelectorAll('.form-control').forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
        });
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
    });
}
