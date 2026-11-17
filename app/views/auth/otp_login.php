<!-- Header: Login WhatsApp OTP & nnaco.sa Aksesoris -->
<div class="auth-brand-badge">
    <span>💬 WhatsApp Authentication</span>
</div>
<h2 class="auth-header-title">Login WhatsApp OTP</h2>
<p class="auth-header-sub">nnaco.sa Aksesoris</p>

<!-- Dynamic Feedback Alert -->
<div id="otpAlert" class="alert d-none border-0 rounded-4 py-2 px-3 small mb-3 text-start" role="alert"></div>

<!-- STEP 1: FORM INPUT NOMOR WHATSAPP -->
<div id="stepRequestOtp">
    <form id="formRequestOtp" onsubmit="handleRequestOtp(event)">
        <input type="hidden" name="csrf_token" id="csrfTokenRequest" value="<?= $csrf_token ?>">

        <!-- WhatsApp Phone Input Pill -->
        <div class="auth-input-group mb-3">
            <label for="phoneNumber" class="auth-label">Nomor WhatsApp Terdaftar</label>
            <div class="input-group">
                <span class="input-group-text rounded-start-pill bg-light border-end-0 px-3 fw-bold text-muted" style="border-color: rgba(226, 219, 235, 0.8);">
                    🇮🇩 +62
                </span>
                <input 
                    type="tel" 
                    class="auth-input-pill rounded-end-pill border-start-0" 
                    id="phoneNumber" 
                    name="phone_number" 
                    required 
                    placeholder="812-xxxx-xxxx atau 0812..."
                    autocomplete="tel"
                    style="border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important;"
                >
            </div>
            <small class="text-muted d-block mt-1 ps-2" style="font-size: 11px;">
                Kode OTP verifikasi akan dikirimkan langsung ke nomor WhatsApp Anda.
            </small>
        </div>

        <!-- Button Kirim OTP -->
        <button type="submit" class="btn-signin-capsule w-100" id="btnSendOtp">
            <span id="btnSendOtpText">Kirim Kode OTP</span>
            <i class="bi bi-arrow-right-short fs-4" id="btnSendOtpIcon"></i>
        </button>
    </form>
</div>

<!-- STEP 2: FORM VERIFIKASI KODE OTP (Hidden Initially) -->
<div id="stepVerifyOtp" class="d-none">

    <!-- Info Nomor Akun & Countdown -->
    <div class="p-3 mb-3 rounded-4 bg-light text-center border" style="border-color: rgba(226, 219, 235, 0.8) !important;">
        <span class="text-muted small d-block mb-1">Kode OTP 6-digit dikirim ke:</span>
        <strong class="fs-6 text-dark font-monospace" id="displayMaskedPhone">+628••••••••</strong>
        <div class="mt-2">
            <span class="badge rounded-pill bg-white text-muted border px-3 py-1 small" id="otpExpiryCountdown">
                <i class="bi bi-clock me-1 text-warning"></i> Berlaku: 05:00
            </span>
        </div>
    </div>

    <form id="formVerifyOtp" onsubmit="handleVerifyOtp(event)">
        <input type="hidden" name="csrf_token" id="csrfTokenVerify" value="<?= $csrf_token ?>">
        <input type="hidden" name="phone_number" id="hiddenPhoneForVerify" value="">

        <!-- Input OTP 6 Digit -->
        <div class="auth-input-group mb-3">
            <label for="otpCode" class="auth-label text-center d-block">Masukkan 6-Digit Kode OTP</label>
            <input 
                type="text" 
                class="auth-input-pill text-center font-monospace fw-bold fs-4 tracking-wider" 
                id="otpCode" 
                name="otp_code" 
                maxlength="6" 
                pattern="[0-9]{6}" 
                inputmode="numeric" 
                required 
                placeholder="••••••" 
                autocomplete="one-time-code"
                style="letter-spacing: 0.4em;"
            >
        </div>

        <!-- Button Verifikasi OTP -->
        <button type="submit" class="btn-signin-capsule w-100 mb-2" id="btnVerifyOtp">
            <span id="btnVerifyText">Verifikasi & Masuk</span>
            <i class="bi bi-shield-check fs-5 ms-1" id="btnVerifyIcon"></i>
        </button>

        <!-- Kirim Ulang OTP & Ganti Nomor Action Row -->
        <div class="row g-2 mt-2 pt-3 border-top" style="border-color: rgba(226, 219, 235, 0.8) !important;">
            <div class="col-6">
                <button type="button" class="btn btn-outline-secondary w-100 rounded-pill py-2 small fw-semibold" id="btnChangePhone" onclick="backToPhoneStep()" style="font-size: 13px; border-color: #cbd5e1; color: #475569;">
                    <i class="bi bi-arrow-left me-1"></i> Ganti Nomor
                </button>
            </div>
            <div class="col-6">
                <button type="button" class="btn btn-outline-primary w-100 rounded-pill py-2 small fw-semibold" id="btnResendOtp" onclick="handleResendOtp()" disabled style="font-size: 13px;">
                    <i class="bi bi-arrow-clockwise me-1"></i> Kirim Ulang (<span id="resendTimer">60</span>s)
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Or Divider -->
<div class="auth-divider-wrap">
    <div class="auth-divider-line"></div>
    <span class="auth-divider-text">pilihan cara masuk lainnya</span>
</div>

<!-- Back to Standard Email/Password Login Button -->
<a href="<?= BASEURL ?>/auth/login" class="btn-wa-capsule text-decoration-none mb-3" style="background: rgba(255, 255, 255, 0.9); color: #4b5563; border: 1.5px solid rgba(226, 219, 235, 0.9);">
    <i class="bi bi-envelope-fill me-2 text-primary"></i>
    <span>Login dengan Email & Password</span>
</a>

<!-- Footer: Don't have an account? Sign up -->
<div class="auth-footer-text">
    <span>Don't have an account?</span>
    <a href="<?= BASEURL ?>/auth/register" class="auth-footer-link">Sign up</a>
</div>

<script>
    const BASE_URL = '<?= BASEURL ?>';
    let currentCsrf = '<?= $csrf_token ?>';
    let expiryInterval = null;
    let cooldownInterval = null;
    let savedPhoneNumber = '';

    function showAlert(msg, type = 'danger') {
        const alertEl = document.getElementById('otpAlert');
        alertEl.className = `alert alert-${type} border-0 rounded-4 py-2 px-3 small mb-3 text-start`;
        alertEl.innerHTML = msg;
        alertEl.classList.remove('d-none');
    }

    function hideAlert() {
        const alertEl = document.getElementById('otpAlert');
        alertEl.classList.add('d-none');
    }

    // Step 1: Request OTP
    function handleRequestOtp(e) {
        e.preventDefault();
        hideAlert();

        const phoneInput = document.getElementById('phoneNumber');
        const phoneVal = phoneInput.value.trim();
        if (!phoneVal) {
            showAlert('Silakan masukkan nomor WhatsApp Anda.');
            return;
        }

        const btn = document.getElementById('btnSendOtp');
        const btnText = document.getElementById('btnSendOtpText');
        const btnIcon = document.getElementById('btnSendOtpIcon');

        btn.disabled = true;
        btnText.textContent = 'Mengirim OTP via WA...';
        btnIcon.className = 'spinner-border spinner-border-sm';

        const formData = new FormData();
        formData.append('csrf_token', currentCsrf);
        formData.append('phone_number', phoneVal);

        fetch(`${BASE_URL}/auth/sendOtp`, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(res => {
            btn.disabled = false;
            btnText.textContent = 'Kirim Kode OTP';
            btnIcon.className = 'bi bi-arrow-right-short fs-4';

            if (res.csrf_token) {
                currentCsrf = res.csrf_token;
                document.getElementById('csrfTokenRequest').value = currentCsrf;
                document.getElementById('csrfTokenVerify').value = currentCsrf;
            }

            if (res.status === 'success') {
                savedPhoneNumber = phoneVal;
                document.getElementById('hiddenPhoneForVerify').value = phoneVal;
                document.getElementById('displayMaskedPhone').textContent = res.phone_masked || phoneVal;

                // Transisi ke Step 2 (Form input kode OTP)
                document.getElementById('stepRequestOtp').classList.add('d-none');
                document.getElementById('stepVerifyOtp').classList.remove('d-none');

                showAlert(res.message, 'success');

                // Start Timers
                startExpiryCountdown(res.expires_in || 300);
                startCooldownTimer(res.cooldown || 60);

                setTimeout(() => {
                    const otpInput = document.getElementById('otpCode');
                    if (otpInput) {
                        otpInput.value = '';
                        otpInput.focus();
                    }
                }, 300);
            } else {
                showAlert(res.message || 'Gagal mengirim OTP ke WhatsApp.');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btnText.textContent = 'Kirim Kode OTP';
            btnIcon.className = 'bi bi-arrow-right-short fs-4';
            showAlert('Terjadi kesalahan jaringan: ' + err.message);
        });
    }

    // Step 2: Verify OTP
    function handleVerifyOtp(e) {
        e.preventDefault();
        hideAlert();

        const otpInput = document.getElementById('otpCode');
        const otpVal = otpInput.value.trim();
        if (otpVal.length !== 6) {
            showAlert('Masukkan 6-digit kode OTP angka yang diterima di WhatsApp.');
            return;
        }

        const btn = document.getElementById('btnVerifyOtp');
        const btnText = document.getElementById('btnVerifyText');
        const btnIcon = document.getElementById('btnVerifyIcon');

        btn.disabled = true;
        btnText.textContent = 'Memverifikasi...';
        btnIcon.className = 'spinner-border spinner-border-sm';

        const formData = new FormData();
        formData.append('csrf_token', currentCsrf);
        formData.append('phone_number', savedPhoneNumber);
        formData.append('otp_code', otpVal);

        fetch(`${BASE_URL}/auth/verifyOtp`, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(res => {
            btn.disabled = false;
            btnText.textContent = 'Verifikasi & Masuk';
            btnIcon.className = 'bi bi-shield-check fs-5 ms-1';

            if (res.csrf_token) {
                currentCsrf = res.csrf_token;
                document.getElementById('csrfTokenRequest').value = currentCsrf;
                document.getElementById('csrfTokenVerify').value = currentCsrf;
            }

            if (res.status === 'success') {
                showAlert(`✅ ${res.message}`, 'success');
                clearInterval(expiryInterval);
                clearInterval(cooldownInterval);
                setTimeout(() => {
                    window.location.href = res.redirect || BASE_URL;
                }, 800);
            } else {
                showAlert(res.message || 'Verifikasi OTP gagal.');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btnText.textContent = 'Verifikasi & Masuk';
            btnIcon.className = 'bi bi-shield-check fs-5 ms-1';
            showAlert('Terjadi kesalahan jaringan: ' + err.message);
        });
    }

    // Resend OTP
    function handleResendOtp() {
        const btnResend = document.getElementById('btnResendOtp');
        if (btnResend.disabled) return;

        btnResend.disabled = true;
        btnResend.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Mengirim ulang...`;

        const formData = new FormData();
        formData.append('csrf_token', currentCsrf);
        formData.append('phone_number', savedPhoneNumber);

        fetch(`${BASE_URL}/auth/sendOtp`, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(res => {
            if (res.csrf_token) {
                currentCsrf = res.csrf_token;
                document.getElementById('csrfTokenRequest').value = currentCsrf;
                document.getElementById('csrfTokenVerify').value = currentCsrf;
            }

            if (res.status === 'success') {
                showAlert(res.message, 'success');
                startExpiryCountdown(res.expires_in || 300);
                startCooldownTimer(res.cooldown || 60);
                const otpInput = document.getElementById('otpCode');
                if (otpInput) {
                    otpInput.value = '';
                    otpInput.focus();
                }
            } else {
                showAlert(res.message || 'Gagal mengirim ulang OTP.');
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

    function backToPhoneStep() {
        clearInterval(expiryInterval);
        clearInterval(cooldownInterval);
        document.getElementById('stepVerifyOtp').classList.add('d-none');
        document.getElementById('stepRequestOtp').classList.remove('d-none');
        hideAlert();
    }

    function startExpiryCountdown(seconds) {
        clearInterval(expiryInterval);
        let remain = seconds;
        const badge = document.getElementById('otpExpiryCountdown');

        function update() {
            if (remain <= 0) {
                clearInterval(expiryInterval);
                badge.innerHTML = `<i class="bi bi-exclamation-triangle-fill text-danger me-1"></i> <span class="text-danger">Kedaluwarsa</span>`;
                showAlert('Kode OTP telah kedaluwarsa. Silakan klik tombol "Kirim Ulang" untuk mendapatkan kode baru di WhatsApp Anda.');
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
        const btn = document.getElementById('btnResendOtp');
        btn.disabled = true;

        function update() {
            if (remain <= 0) {
                clearInterval(cooldownInterval);
                btn.disabled = false;
                btn.innerHTML = `<i class="bi bi-arrow-clockwise"></i> Kirim Ulang OTP`;
                return;
            }
            btn.innerHTML = `<i class="bi bi-arrow-clockwise"></i> Kirim Ulang (<span id="resendTimer">${remain}</span>s)`;
            remain--;
        }

        update();
        cooldownInterval = setInterval(update, 1000);
    }
</script>
