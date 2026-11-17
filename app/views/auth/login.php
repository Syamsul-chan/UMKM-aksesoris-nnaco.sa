<!-- Header: Welcome Back & nnaco.sa Aksesoris -->
<div class="auth-brand-badge">
    <span class="auth-bear-icon">🐻</span>
    <span>STORE LOGIN</span>
</div>
<h2 class="auth-header-title">Welcome Back</h2>
<p class="auth-header-sub">nnaco.sa Aksesoris</p>

<!-- Login Form -->
<form action="<?= BASEURL ?>/auth/login" method="POST">
    <input type="hidden" name="csrf_token" value="<?= Controller::generateCsrfToken() ?>">

    <!-- Email Address or Username Input Pill -->
    <div class="auth-input-group">
        <label for="email" class="auth-label">Email Address / Username</label>
        <input 
            type="text" 
            class="auth-input-pill" 
            id="email" 
            name="email" 
            required 
            placeholder="admin@nnaco.sa atau admin"
            autocomplete="username"
        >
    </div>

    <!-- Password Input Pill -->
    <div class="auth-input-group mb-2">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label for="password" class="auth-label mb-0">Password</label>
            <a href="<?= BASEURL ?>/auth/forgotPassword" class="auth-forgot-link">Lupa kata sandi?</a>
        </div>
        <div class="auth-password-wrap">
            <input 
                type="password" 
                class="auth-input-pill pe-5" 
                id="password" 
                name="password" 
                required 
                placeholder="••••••••••••"
                autocomplete="current-password"
            >
            <button type="button" class="auth-toggle-pwd" id="togglePasswordBtn" title="Lihat / Sembunyikan Password" tabindex="-1">
                <i class="bi bi-eye" id="togglePasswordIcon"></i>
            </button>
        </div>
    </div>

    <!-- Sign In Button (Sky/Cyan Blue Gradient) -->
    <button type="submit" class="btn-signin-capsule">
        <span>Sign In</span>
        <i class="bi bi-arrow-right-short fs-4"></i>
    </button>

    <!-- Or Divider -->
    <div class="auth-divider-wrap">
        <div class="auth-divider-line"></div>
        <span class="auth-divider-text">atau login lebih cepat</span>
    </div>

    <!-- WhatsApp OTP Login Button -->
    <a href="<?= BASEURL ?>/auth/otp" class="btn-wa-capsule">
        <i class="bi bi-whatsapp fs-5 text-success"></i>
        <span>Login dengan WhatsApp OTP</span>
    </a>

    <!-- Footer: Don't have an account? Sign up -->
    <div class="auth-footer-text">
        <span>Don't have an account?</span>
        <a href="<?= BASEURL ?>/auth/register" class="auth-footer-link">Sign up</a>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const pwdInput = document.getElementById('password');
    const pwdIcon = document.getElementById('togglePasswordIcon');

    if (toggleBtn && pwdInput && pwdIcon) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                pwdIcon.classList.remove('bi-eye');
                pwdIcon.classList.add('bi-eye-slash');
            } else {
                pwdInput.type = 'password';
                pwdIcon.classList.remove('bi-eye-slash');
                pwdIcon.classList.add('bi-eye');
            }
        });
    }
});
</script>
