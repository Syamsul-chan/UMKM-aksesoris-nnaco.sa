<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Login | nnaco.sa Aksesoris' ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Micro Interactions CSS -->
    <link rel="stylesheet" href="<?= BASEURL ?>/css/micro-interactions.css?v=<?= time() ?>">
    
    <style>
        /* ==============================================================
           NNACO.SA - MODERN MINIMALIST NEUMORPHIC AUTH THEME
           Palette: Light Gray Canvas (#f1f5f9), Pure White (#ffffff),
                    Deep Navy Blue (#0f172a), Sky/Cyan Blue (#0ea5e9)
           ============================================================== */
        *, *::before, *::after {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            width: 100vw;
            height: 100vh;
            min-height: 100vh;
            overflow-x: hidden;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f1f5f9;
            color: #0f172a;
            -webkit-font-smoothing: antialiased;
        }

        /* Clean Modern Ambient Background */
        .auth-viewport {
            position: relative;
            width: 100vw;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: 
                radial-gradient(circle at 15% 15%, rgba(14, 165, 233, 0.08) 0%, transparent 45%),
                radial-gradient(circle at 85% 85%, rgba(14, 165, 233, 0.06) 0%, transparent 45%),
                linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 30px 20px;
        }

        /* Subtle Neumorphic Background Circles (Minimalist) */
        .auth-bg-circle {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            z-index: 1;
        }

        .auth-bg-circle.c1 {
            width: 480px;
            height: 480px;
            top: -120px;
            right: -100px;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.08) 0%, rgba(255, 255, 255, 0) 70%);
        }

        .auth-bg-circle.c2 {
            width: 520px;
            height: 520px;
            bottom: -150px;
            left: -120px;
            background: radial-gradient(circle, rgba(15, 23, 42, 0.04) 0%, rgba(255, 255, 255, 0) 70%);
        }

        /* Center Login Container */
        .auth-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 440px;
            margin: 0 auto;
        }

        /* Neumorphic Pure White Card */
        .auth-card-floating {
            background: #ffffff;
            border-radius: 28px;
            border: 1px solid #e2e8f0;
            box-shadow: 
                0 20px 45px -10px rgba(15, 23, 42, 0.08),
                0 4px 16px rgba(15, 23, 42, 0.03),
                inset 0 1px 0 rgba(255, 255, 255, 0.9);
            padding: 42px 36px 38px 36px;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .auth-card-floating:hover {
            box-shadow: 
                0 25px 55px -8px rgba(15, 23, 42, 0.12),
                0 6px 20px rgba(15, 23, 42, 0.04);
        }

        /* Brand Badge Header */
        .auth-brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #e0f2fe;
            border: 1px solid #bae6fd;
            padding: 6px 16px;
            border-radius: 50px;
            margin-bottom: 14px;
        }

        .auth-brand-badge span {
            font-size: 11px;
            font-weight: 800;
            color: #0284c7;
            letter-spacing: 0.6px;
            text-transform: uppercase;
        }

        .auth-brand-badge .auth-bear-icon {
            font-size: 13px;
        }

        .auth-header-title {
            font-size: 26px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            line-height: 1.2;
            letter-spacing: -0.4px;
        }

        .auth-header-sub {
            font-size: 13.5px;
            font-weight: 500;
            color: #64748b;
            margin-top: 6px;
            margin-bottom: 26px;
        }

        /* Input Fields: Clean Neumorphic */
        .auth-input-group {
            margin-bottom: 18px;
            text-align: left;
        }

        .auth-label {
            display: block;
            font-size: 12.5px;
            font-weight: 700;
            color: #475569;
            margin-bottom: 7px;
            margin-left: 4px;
            letter-spacing: 0.2px;
        }

        .auth-input-pill {
            width: 100%;
            height: 50px;
            border-radius: 14px;
            background-color: #f8fafc;
            border: 1.5px solid #e2e8f0;
            padding: 0 18px;
            font-size: 14px;
            font-weight: 500;
            color: #0f172a;
            outline: none;
            box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.03);
            transition: all 0.2s ease;
        }

        .auth-input-pill::placeholder {
            color: #94a3b8;
            font-size: 13.5px;
            font-weight: 400;
        }

        .auth-input-pill:focus {
            background-color: #ffffff;
            border-color: #0ea5e9;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.18);
        }

        .auth-password-wrap {
            position: relative;
        }

        .auth-toggle-pwd {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 6px;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
        }

        .auth-toggle-pwd:hover {
            color: #0ea5e9;
        }

        .auth-forgot-link {
            font-size: 12px;
            font-weight: 600;
            color: #0284c7;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .auth-forgot-link:hover {
            color: #0369a1;
            text-decoration: underline;
        }

        /* Sign In Button: Sky/Cyan Blue Gradient with Soft Shadow */
        .btn-signin-capsule {
            width: 100%;
            height: 50px;
            border-radius: 14px;
            border: none;
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            box-shadow: 0 8px 20px rgba(14, 165, 233, 0.32);
            font-size: 15px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: 0.3px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 10px;
        }

        .btn-signin-capsule:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 26px rgba(14, 165, 233, 0.42);
            color: #ffffff;
        }

        .btn-signin-capsule:active {
            transform: translateY(0);
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.25);
        }

        /* Or Divider */
        .auth-divider-wrap {
            position: relative;
            text-align: center;
            margin: 22px 0 16px 0;
        }

        .auth-divider-line {
            border-top: 1px solid #e2e8f0;
        }

        .auth-divider-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #ffffff;
            padding: 0 14px;
            font-size: 11.5px;
            font-weight: 600;
            color: #94a3b8;
        }

        /* WhatsApp OTP Capsule */
        .btn-wa-capsule {
            width: 100%;
            height: 48px;
            border-radius: 14px;
            background: #f0fdf4;
            border: 1.5px solid #86efac;
            color: #15803d;
            font-size: 13.5px;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(22, 163, 74, 0.06);
        }

        .btn-wa-capsule:hover {
            background: #dcfce7;
            border-color: #4ade80;
            color: #166534;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(22, 163, 74, 0.15);
        }

        /* Footer Link */
        .auth-footer-text {
            margin-top: 22px;
            font-size: 13px;
            color: #64748b;
            font-weight: 500;
        }

        .auth-footer-link {
            color: #0ea5e9;
            font-weight: 700;
            text-decoration: none;
            transition: color 0.2s;
            margin-left: 4px;
        }

        .auth-footer-link:hover {
            color: #0284c7;
            text-decoration: underline;
        }

        /* Responsive Mobile Tuning */
        @media (max-width: 576px) {
            .auth-card-floating {
                padding: 32px 24px 28px 24px;
                border-radius: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-viewport">
        <!-- Ambient Neumorphic Background Accents -->
        <div class="auth-bg-circle c1"></div>
        <div class="auth-bg-circle c2"></div>

        <!-- Center Login Card Container -->
        <div class="auth-container">
            <?php if(isset($_SESSION['alert'])): ?>
                <div class="alert alert-<?= $_SESSION['alert']['type'] ?> alert-dismissible fade show rounded-4 px-4 py-2.5 border-0 shadow-sm mb-3 text-center small fw-semibold" role="alert" style="background: #ffffff; border: 1px solid #e2e8f0 !important; color: #0f172a;">
                    <?= $_SESSION['alert']['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['alert']); ?>
            <?php endif; ?>

            <div class="auth-card-floating text-center">
                <?php require_once '../app/views/' . $view . '.php'; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Micro Interactions JS -->
    <script src="<?= BASEURL ?>/js/micro-interactions.js?v=<?= time() ?>"></script>
</body>
</html>
