<?php

class AuthMiddleware {
    /**
     * Memeriksa apakah user sudah login dan memiliki role yang diizinkan.
     * Jika belum login, redirect ke halaman login.
     * Jika role tidak sesuai, redirect ke dashboard masing-masing.
     * 
     * @param array $allowedRoles Array role name (misal: ['Admin', 'Operator'])
     */
    public static function check($allowedRoles = []) {
        // Jika belum login, selalu arahkan ke halaman auth
        if (!isset($_SESSION['user'])) {
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }

        // Jika ada spesifik role yang diizinkan, cek apakah role user ada di dalamnya
        if (!empty($allowedRoles)) {
            $userRole = $_SESSION['user']['role'];
            if (!in_array($userRole, $allowedRoles)) {
                // Return 403 Forbidden
                require_once '../app/controllers/ErrorController.php';
                $errorController = new ErrorController();
                $errorController->forbidden();
                exit;
            }
        }
    }

    /**
     * Memeriksa jika user sudah login (berguna untuk halaman login/register 
     * agar user tidak bisa akses jika sudah login)
     */
    public static function checkGuest() {
        if (isset($_SESSION['user'])) {
            self::redirectDashboard($_SESSION['user']['role']);
        }
    }

    /**
     * Mengarahkan user ke dashboard sesuai role-nya
     */
    public static function redirectDashboard($roleName) {
        $roleName = strtolower($roleName);
        switch ($roleName) {
            case 'admin':
                header('Location: ' . BASEURL . '/admin');
                break;
            case 'operator':
                header('Location: ' . BASEURL . '/operator');
                break;
            case 'owner':
                header('Location: ' . BASEURL . '/owner');
                break;
            case 'customer':
                header('Location: ' . BASEURL . '/customer');
                break;
            default:
                header('Location: ' . BASEURL);
                break;
        }
        exit;
    }
}
