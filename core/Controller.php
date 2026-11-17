<?php

class Controller {
    public function view($view, $data = []) {
        // Make $data keys available as variables
        extract($data);
        $layout = $layout ?? 'main';
        require_once '../app/views/layouts/' . $layout . '.php';
    }

    public function model($model) {
        require_once '../app/models/' . $model . '.php';
        return new $model;
    }

    public static function generateCsrfToken() {
        if (session_status() == PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrfToken($token) {
        if (session_status() == PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
        if (!empty($token) && is_string($token) && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
            return true;
        }
        return false;
    }
}
