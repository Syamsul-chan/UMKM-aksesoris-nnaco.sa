<?php
session_start();
// Bootstraping
require_once '../config/config.php';
require_once '../core/App.php';
require_once '../core/Controller.php';
require_once '../core/Database.php';
require_once '../core/AuthMiddleware.php';

try {
    $app = new App();
} catch (Throwable $e) {
    // Log error here if needed: error_log($e->getMessage());
    require_once '../app/controllers/ErrorController.php';
    $errorController = new ErrorController();
    $errorController->serverError();
}
