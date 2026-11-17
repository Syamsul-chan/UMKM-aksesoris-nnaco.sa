<div class="auth-brand-badge">
    <span>✨ Join Member</span>
</div>
<h2 class="auth-header-title">Create Account</h2>
<p class="auth-header-sub">nnaco.sa Aksesoris</p>

<form action="<?= BASEURL ?>/auth/register" method="POST">
    <input type="hidden" name="csrf_token" value="<?= Controller::generateCsrfToken() ?>">
    
    <div class="auth-input-group">
        <label for="username" class="auth-label">Full Name</label>
        <input type="text" class="auth-input-pill" id="username" name="username" required placeholder="Nama Lengkap">
    </div>

    <div class="auth-input-group">
        <label for="email" class="auth-label">Email Address</label>
        <input type="email" class="auth-input-pill" id="email" name="email" required placeholder="name@example.com">
    </div>

    <div class="auth-input-group">
        <label for="phone_number" class="auth-label">Nomor WhatsApp (Aktif untuk Login OTP)</label>
        <input type="tel" class="auth-input-pill" id="phone_number" name="phone_number" placeholder="08xxxxxxxxxx atau 628xxxxxxxxxx">
        <small class="text-muted d-block mt-1 ps-2" style="font-size: 11px;">Digunakan untuk login cepat dengan kode OTP WhatsApp.</small>
    </div>

    <div class="auth-input-group">
        <label for="password" class="auth-label">Password</label>
        <input type="password" class="auth-input-pill" id="password" name="password" required placeholder="••••••••">
    </div>

    <div class="auth-input-group mb-2">
        <label for="confirm_password" class="auth-label">Confirm Password</label>
        <input type="password" class="auth-input-pill" id="confirm_password" name="confirm_password" required placeholder="••••••••">
    </div>

    <button type="submit" class="btn-signin-capsule">
        <span>Sign Up</span>
        <i class="bi bi-arrow-right-short fs-4"></i>
    </button>

    <div class="auth-footer-text">
        <span>Already have an account?</span>
        <a href="<?= BASEURL ?>/auth/login" class="auth-footer-link">Sign in</a>
    </div>
</form>
