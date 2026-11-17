<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Dashboard | nnaco_sa' ?></title>
    <!-- Anti-flash Dashboard Theme Initialization -->
    <script>
        (function() {
            try {
                const savedTheme = localStorage.getItem('nnaco_dashboard_theme') || 'light';
                document.documentElement.setAttribute('data-theme', savedTheme);
            } catch(e) {}
        })();
    </script>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts: Quicksand & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Quicksand:wght@500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Kawaii 3D Showcase CSS -->
    <link rel="stylesheet" href="<?= BASEURL ?>/css/dashboard-gradient.css?v=<?= time() ?>">
    <!-- Micro Interactions CSS (Button Active, Card Hover/Click, Skeleton Shimmer) -->
    <link rel="stylesheet" href="<?= BASEURL ?>/css/micro-interactions.css?v=<?= time() ?>">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="dashboard-gradient-theme">
    
    <!-- Master Dashboard Shell Container -->
    <div class="d-shell">
        <!-- Left Lavender Sidebar -->
        <?php require_once '../app/views/components/sidebar.php'; ?>

        <!-- Main Content Area -->
        <div class="d-main-wrapper">
            <?php 
                $isDashboardIndex = ($view === 'home/index' || $view === 'admin/index' || $view === 'owner/index');
            ?>
            <?php if($isDashboardIndex): ?>
                <?php require_once '../app/views/' . $view . '.php'; ?>
            <?php else: ?>
                <main class="d-subpage-container" style="flex: 1; padding: 30px; background: #FFFDF9; overflow-y: auto;">
                    <?php if(file_exists('../app/views/components/alert.php')) require_once '../app/views/components/alert.php'; ?>
                    <?php require_once '../app/views/' . $view . '.php'; ?>
                </main>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modals & AI Chatbot -->
    <?php if(file_exists('../app/views/components/modal.php')) require_once '../app/views/components/modal.php'; ?>
    <?php if(file_exists('../app/views/components/chatbot.php')) require_once '../app/views/components/chatbot.php'; ?>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Micro Interactions JS (Dynamic Ripple & Skeleton Shimmer) -->
    <script src="<?= BASEURL ?>/js/micro-interactions.js?v=<?= time() ?>"></script>

    <!-- Dashboard Theme Toggle Script (Dark / Light Mode) -->
    <script>
        function applyDashboardTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            try {
                localStorage.setItem('nnaco_dashboard_theme', theme);
            } catch(e) {}

            const icon = document.getElementById('themeIcon');
            const name = document.getElementById('themeModeName');
            const switchEl = document.getElementById('themeSwitchVisual');

            if (icon && name) {
                if (theme === 'dark') {
                    icon.className = 'bi bi-moon-stars-fill text-info';
                    name.textContent = 'Dark Mode';
                    if (switchEl) switchEl.setAttribute('title', 'Beralih ke Light Mode');
                } else {
                    icon.className = 'bi bi-sun text-warning';
                    name.textContent = 'Light Mode';
                    if (switchEl) switchEl.setAttribute('title', 'Beralih ke Dark Mode');
                }
            }
        }

        function toggleDashboardTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const nextTheme = (currentTheme === 'dark') ? 'light' : 'dark';
            applyDashboardTheme(nextTheme);
        }

        document.addEventListener('DOMContentLoaded', function() {
            try {
                const savedTheme = localStorage.getItem('nnaco_dashboard_theme') || 'light';
                applyDashboardTheme(savedTheme);
            } catch(e) {
                applyDashboardTheme('light');
            }
        });
    </script>
</body>
</html>
