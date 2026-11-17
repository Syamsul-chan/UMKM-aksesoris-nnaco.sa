<?php

class ErrorController extends Controller {
    public function notFound() {
        http_response_code(404);
        $data = [
            'title' => '404 Not Found',
            'layout' => 'main' // Using main layout
        ];
        $this->view('errors/404', $data);
    }

    public function forbidden() {
        http_response_code(403);
        $data = [
            'title' => '403 Forbidden',
            'layout' => 'main'
        ];
        $this->view('errors/403', $data);
    }

    public function serverError() {
        http_response_code(500);
        $data = [
            'title' => '500 Internal Server Error',
            'layout' => 'main'
        ];
        $this->view('errors/500', $data);
    }
}
