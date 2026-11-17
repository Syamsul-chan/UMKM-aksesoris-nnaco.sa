<?php

class CustomerController extends Controller {
    public function __construct() {
        AuthMiddleware::check(['Customer']);
    }

    public function index() {
        $data = [
            'title' => 'Customer Dashboard | nnaco_sa',
            'view' => 'customer/index',
            'breadcrumbs' => [
                'Home' => BASEURL,
                'Customer Area' => '#'
            ]
        ];
        $this->view('customer/index', $data);
    }
}
