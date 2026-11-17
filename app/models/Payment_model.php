<?php

class Payment_model {
    private $table = 'payments';
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getPaymentByOrderId($order_id) {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE order_id = :order_id');
        $this->db->bind('order_id', $order_id);
        return $this->db->single();
    }
}
