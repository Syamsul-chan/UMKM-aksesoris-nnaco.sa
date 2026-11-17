<!-- Header: Lupa Kata Sandi & nnaco.sa Aksesoris -->
<div class="auth-brand-badge">
    <span>🔐 Reset Password</span>
</div>
<h2 class="auth-header-title">Lupa Kata Sandi</h2>
<p class="auth-header-sub">nnaco.sa Aksesoris</p>

<!-- Dynamic Feedback Alert -->
<div id="forgotAlert" class="alert d-none border-0 rounded-4 py-2 px-3 small mb-3 text-start" role="alert"></div>

<!-- STEP 1: FORM INPUT EMAIL / WHATSAPP -->
<div id="stepRequestReset">
    <p class="text-muted small mb-3 text-center" style="font-size: 13px; line-height: 1.5;">
        Masukkan alamat email atau nomor WhatsApp yang terdaftar pada akun Anda. Kami akan mengirimkan kode verifikasi 6-digit untuk mereset kata sandi.
    </p>

    <form id="formRequestReset" onsubmit="handleRequestReset(event)">
        <input type="hidden" name="csrf_token" id="csrfTokenRequest" value="<?= $csrf_token ?>">

        <!-- Identity Input Pill -->
        <div class="auth-input-group mb-3 text-start">
            <label for="identityInput" class="auth-label">Email atau Nomor WhatsApp Terdaftar</label>
            <input 
                type="text" 
                class="auth-input-pill" 
                id="identityInput" 
                name="identity" 
                required 
                placeholder="nama@email.com atau 08123456789"
                autocomplete="username"
            >
            <small class="text-muted d-block mt-1 ps-2" style="font-size: 11px;">
                Dapat berupa alamat email akun atau nomor WhatsApp yang terhubung.
            </small>
        </div>

        <!-- Button Kirim Kode Verifikasi -->
        <button type="submit" class="btn-signin-capsule w-100" id="btnSendReset">
            <span id="btnSendResetText">Kirim Kode Verifikasi</span>
            <i class="bi bi-arrow-right-short fs-4" id="btnSendResetIcon"></i>
        </button>
    </form>
</div>

<!-- STEP 2: FORM MASUKKAN KODE & KATA SANDI BARU (Hidden Initially) -->
<div id="stepVerifyReset" class="d-none">
    <div class="p-3 mb-3 rounded-4 bg-light text-center border" style="border-color: rgba(226, 219, 235, 0.8) !important;">
        <span class="text-muted small d-block mb-1">Kode verifikasi 6-digit dikirim ke:</span>
        <strong class="fs-6 text-dark font-monospace" id="displayTarget">...</strong>
        <div class="mt-2">
            <span class="badge rounded-pill bg-white text-muted border px-3 py-1 small" id="resetExpiryCountdown">
                <i class="bi bi-clock me-1 text-warning"></i> Berlaku: 15:00
            </span>
        </div>
    </div>

    <form id="formSubmitReset" onsubmit="handleSubmitReset(event)">
        <input type="hidden" name="csrf_token" id="csrfTokenSubmit" value="<?= $csrf_token ?>">

        <!-- Input 6 Digit Kode Verifikasi -->
        <div class="auth-input-group mb-3 text-start">
            <label for="resetCodeInput" class="auth-label text-center d-block">Masukkan 6-Digit Kode Verifikasi</label>
            <input 
                type="text" 
                class="auth-input-pill text-center font-monospace fw-bold fs-4 tracking-wider" 
                id="resetCodeInput" 
                name="reset_code" 
                maxlength="6" 
                pattern="[0-9]{6}" 
                inputmode="numeric" 
                required 
                placeholder="••••••" 
                autocomplete="one-time-code"
                style="letter-spacing: 0.4em;"
            >
        </div>

        <!-- Input Kata Sandi Baru -->
        <div class="auth-input-group mb-3 text-start">
            <label for="newPassword" class="auth-label">Kata Sandi Baru</label>
            <div class="auth-password-wrap">
                <input 
                    type="password" 
                    class="auth-input-pill pe-5" 
                    id="newPassword" 
                    name="password" 
                    required 
                    minlength="6"
                    placeholder="Minimal 6 karakter"
                    autocomplete="new-password"
                >
                <button type="button" class="auth-toggle-pwd" onclick="togglePasswordVisibility('newPassword', 'toggleNewIcon')" title="Lihat/Sembunyikan" tabindex="-1">
                    <i class="bi bi-eye" id="toggleNewIcon"></i>
                </button>
            </div>
        </div>

        <!-- Input Konfirmasi Kata Sandi Baru -->
        <div class="auth-input-group mb-3 text-start">
            <label for="confirmNewPassword" class="auth-label">Konfirmasi Kata Sandi Baru</label>
            <div class="auth-password-wrap">
                <input 
                    type="password" 
                    class="auth-input-pill pe-5" 
                    id="confirmNewPassword" 
                    name="confirm_password" 
                    required 
                    minlength="6"
                    placeholder="Ulangi kata sandi baru"
                    autocomplete="new-password"
                >
                <button type="button" class="auth-toggle-pwd" onclick="togglePasswordVisibility('confirmNewPassword', 'toggleConfirmIcon')" title="Lihat/Sembunyikan" tabindex="-1">
                    <i class="bi bi-eye" id="toggleConfirmIcon"></i>
                </button>
            </div>
        </div>

        <!-- Button Simpan Kata Sandi Baru -->
        <button type="submit" class="btn-signin-capsule w-100 mb-2" id="btnSubmitReset">
            <span id="btnSubmitResetText">Simpan Kata Sandi Baru</span>
            <i class="bi bi-shield-check fs-5 ms-1" id="btnSubmitResetIcon"></i>
        </button>

        <!-- Kirim Ulang Kode & Ganti Identitas Action Row -->
        <div class="row g-2 mt-2 pt-3 border-top" style="border-color: rgba(226, 219, 235, 0.8) !important;">
            <div class="col-6">
                <button type="button" class="btn btn-outline-secondary w-100 rounded-pill py-2 small fw-semibold" id="btnChangeIdentity" onclick="backToIdentityStep()" style="font-size: 13px; border-color: #cbd5e1; color: #475569;">
                    <i class="bi bi-arrow-left me-1"></i> Ganti Akun
                </button>
            </div>
            <div class="col-6">
                <button type="button" class="btn btn-outline-primary w-100 rounded-pill py-2 small fw-semibold" id="btnResendReset" onclick="handleResendReset()" disabled style="font-size: 13px;">
                    <i class="bi bi-arrow-clockwise me-1"></i> Kirim Ulang (<span id="resendTimer">60</span>s)
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Or Divider -->
<div class="auth-divider-wrap">
    <div class="auth-divider-line"></div>
    <span class="auth-divider-text">atau kembali</span>
</div>

<!-- Back to Login Button -->
<a href="<?= BASEURL ?>/auth/login" class="btn-wa-capsule text-decoration-none mb-3" style="background: rgba(255, 255, 255, 0.9); color: #4b5563; border: 1.5px solid rgba(226, 219, 235, 0.9);">
    <i class="bi bi-box-arrow-in-left me-2 text-primary"></i>
    <span>Kembali ke Halaman Login</span>
</a>

<!-- Footer: Belum punya akun? Sign up -->
<div class="auth-footer-text">
    <span>Belum punya akun?</span>
    <a href="<?= BASEURL ?>/auth/register" class="auth-footer-link">Sign up</a>
</div>

<script>
    const BASE_URL = '<?= BASEURL ?>';
    let currentCsrf = '<?= $csrf_token ?>';
    let expiryInterval = null;
    let cooldownInterval = null;
    let savedIdentity = '';

    function showAlert(msg, type = 'danger') {
        const alertEl = document.getElementById('forgotAlert');
        alertEl.className = `alert alert-${type} border-0 rounded-4 py-2 px-3 small mb-3 text-start`;
        alertEl.innerHTML = msg;
        alertEl.classList.remove('d-none');
    }

    function hideAlert() {
        const alertEl = document.getElementById('forgotAlert');
        alertEl.classList.add('d-none');
    }

    function togglePasswordVisibility(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input && icon) {
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }
    }

    // Step 1: Request Reset Code
    function handleRequestReset(e) {
        e.preventDefault();
        hideAlert();

        const input = document.getElementById('identityInput');
        const identityVal = input.value.trim();
        if (!identityVal) {
            showAlert('Silakan masukkan alamat email atau nomor WhatsApp terdaftar.');
            return;
        }

        const btn = document.getElementById('btnSendReset');
        const btnText = document.getElementById('btnSendResetText');
        const btnIcon = document.getElementById('btnSendResetIcon');

        btn.disabled = true;
        btnText.textContent = 'Memeriksa & Mengirim...';
        btnIcon.className = 'spinner-border spinner-border-sm';

        const formData = new FormData();
        formData.append('csrf_token', currentCsrf);
        formData.append('identity', identityVal);

        fetch(`${BASE_URL}/auth/sendResetCode`, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(res => {
            btn.disabled = false;
            btnText.textContent = 'Kirim Kode Verifikasi';
            btnIcon.className = 'bi bi-arrow-right-short fs-4';

            if (res.csrf_token) {
                currentCsrf = res.csrf_token;
                document.getElementById('csrfTokenRequest').value = currentCsrf;
                document.getElementById('csrfTokenSubmit').value = currentCsrf;
            }

            if (res.status === 'success') {
                savedIdentity = identityVal;
                document.getElementById('displayTarget').textContent = res.dest_desc || identityVal;

                // Transisi ke Step 2
                document.getElementById('stepRequestReset').classList.add('d-none');
                document.getElementById('stepVerifyReset').classList.remove('d-none');

                let successMsg = res.message;
                if (res.dev_hint) {
                    successMsg += `<br><strong class="text-danger mt-1 d-block"><i class="bi bi-code-slash"></i> ${res.dev_hint}</strong>`;
                }
                showAlert(successMsg, 'success');

                // Timers
                startExpiryCountdown(res.expires_in || 900);
                startCooldownTimer(res.cooldown || 60);

                setTimeout(() => {
                    const codeInput = document.getElementById('resetCodeInput');
                    if (codeInput) {
                        codeInput.focus();
                        if (res.dev_code) codeInput.value = res.dev_code;
                    }
                }, 300);
            } else {
                showAlert(res.message || 'Gagal mengirim kode verifikasi.');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btnText.textContent = 'Kirim Kode Verifikasi';
            btnIcon.className = 'bi bi-arrow-right-short fs-4';
            showAlert('Terjadi kesalahan jaringan: ' + err.message);
        });
    }

    // Step 2: Submit Reset Password
    function handleSubmitReset(e) {
        e.preventDefault();
        hideAlert();

        const codeInput = document.getElementById('resetCodeInput');
        const codeVal = codeInput.value.trim();
        const pwdInput = document.getElementById('newPassword');
        const pwdVal = pwdInput.value;
        const confirmInput = document.getElementById('confirmNewPassword');
        const confirmVal = confirmInput.value;

        if (codeVal.length !== 6) {
            showAlert('Masukkan 6-digit kode verifikasi angka.');
            return;
        }

        if (pwdVal.length < 6) {
            showAlert('Kata sandi baru minimal 6 karakter.');
            return;
        }

        if (pwdVal !== confirmVal) {
            showAlert('Konfirmasi kata sandi baru tidak cocok.');
            return;
        }

        const btn = document.getElementById('btnSubmitReset');
        const btnText = document.getElementById('btnSubmitResetText');
        const btnIcon = document.getElementById('btnSubmitResetIcon');

        btn.disabled = true;
        btnText.textContent = 'Menyimpan Kata Sandi...';
        btnIcon.className = 'spinner-border spinner-border-sm';

        const formData = new FormData();
        formData.append('csrf_token', currentCsrf);
        formData.append('identity', savedIdentity);
        formData.append('reset_code', codeVal);
        formData.append('password', pwdVal);
        formData.append('confirm_password', confirmVal);

        fetch(`${BASE_URL}/auth/submitResetPassword`, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(res => {
            btn.disabled = false;
            btnText.textContent = 'Simpan Kata Sandi Baru';
            btnIcon.className = 'bi bi-shield-check fs-5 ms-1';

            if (res.csrf_token) {
                currentCsrf = res.csrf_token;
                document.getElementById('csrfTokenRequest').value = currentCsrf;
                document.getElementById('csrfTokenSubmit').value = currentCsrf;
            }

            if (res.status === 'success') {
                showAlert(`✅ ${res.message}`, 'success');
                clearInterval(expiryInterval);
                clearInterval(cooldownInterval);
                setTimeout(() => {
                    window.location.href = res.redirect || `${BASE_URL}/auth/login`;
                }, 800);
            } else {
                showAlert(res.message || 'Gagal mereset kata sandi.');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btnText.textContent = 'Simpan Kata Sandi Baru';
            btnIcon.className = 'bi bi-shield-check fs-5 ms-1';
            showAlert('Terjadi kesalahan jaringan: ' + err.message);
        });
    }

    // Resend Reset Code
    function handleResendReset() {
        const btnResend = document.getElementById('btnResendReset');
        if (btnResend.disabled) return;

        btnResend.disabled = true;
        btnResend.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Mengirim ulang...`;

        const formData = new FormData();
        formData.append('csrf_token', currentCsrf);
        formData.append('identity', savedIdentity);

        fetch(`${BASE_URL}/auth/sendResetCode`, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(res => {
            if (res.csrf_token) {
                currentCsrf = res.csrf_token;
                document.getElementById('csrfTokenRequest').value = currentCsrf;
                document.getElementById('csrfTokenSubmit').value = currentCsrf;
            }

            if (res.status === 'success') {
                let successMsg = res.message;
                if (res.dev_hint) {
                    successMsg += `<br><strong class="text-danger mt-1 d-block"><i class="bi bi-code-slash"></i> ${res.dev_hint}</strong>`;
                }
                showAlert(successMsg, 'success');
                startExpiryCountdown(res.expires_in || 900);
                startCooldownTimer(res.cooldown || 60);

                if (res.dev_code) {
                    document.getElementById('resetCodeInput').value = res.dev_code;
                }
            } else {
                showAlert(res.message || 'Gagal mengirim ulang kode.');
                btnResend.disabled = false;
                btnResend.innerHTML = `<i class="bi bi-arrow-clockwise me-1"></i> Kirim Ulang`;
            }
        })
        .catch(err => {
            showAlert('Gagal menghubungi server: ' + err.message);
            btnResend.disabled = false;
            btnResend.innerHTML = `<i class="bi bi-arrow-clockwise me-1"></i> Kirim Ulang`;
        });
    }

    function backToIdentityStep() {
        clearInterval(expiryInterval);
        clearInterval(cooldownInterval);
        document.getElementById('stepVerifyReset').classList.add('d-none');
        document.getElementById('stepRequestReset').classList.remove('d-none');
        hideAlert();
    }

    function startExpiryCountdown(seconds) {
        clearInterval(expiryInterval);
        let remain = seconds;
        const badge = document.getElementById('resetExpiryCountdown');

        function update() {
            if (remain <= 0) {
                clearInterval(expiryInterval);
                badge.innerHTML = `<i class="bi bi-exclamation-triangle-fill text-danger me-1"></i> <span class="text-danger">Kedaluwarsa</span>`;
                showAlert('Kode verifikasi telah kedaluwarsa. Silakan klik tombol "Kirim Ulang" untuk mendapatkan kode baru.');
                return;
            }
            const mins = Math.floor(remain / 60).toString().padStart(2, '0');
            const secs = (remain % 60).toString().padStart(2, '0');
            badge.innerHTML = `<i class="bi bi-clock me-1 text-warning"></i> Berlaku: ${mins}:${secs}`;
            remain--;
        }

        update();
        expiryInterval = setInterval(update, 1000);
    }

    function startCooldownTimer(seconds) {
        clearInterval(cooldownInterval);
        let remain = seconds;
        const btn = document.getElementById('btnResendReset');
        btn.disabled = true;

        function update() {
            if (remain <= 0) {
                clearInterval(cooldownInterval);
                btn.disabled = false;
                btn.innerHTML = `<i class="bi bi-arrow-clockwise me-1"></i> Kirim Ulang`;
                return;
            }
            btn.innerHTML = `<i class="bi bi-arrow-clockwise me-1"></i> Kirim Ulang (<span id="resendTimer">${remain}</span>s)`;
            remain--;
        }

        update();
        cooldownInterval = setInterval(update, 1000);
    }
</script>
