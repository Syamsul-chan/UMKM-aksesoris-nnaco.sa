<?php

class Address_model {
    private $table = 'addresses';
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getAddressesByUserId($user_id) {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE user_id = :user_id');
        $this->db->bind('user_id', $user_id);
        return $this->db->resultSet();
    }

    public function getAddressById($id) {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE id = :id');
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function addAddress($data) {
        $this->db->query('INSERT INTO ' . $this->table . ' (user_id, address_line, city, province, postal_code, phone_number) VALUES (:user_id, :address_line, :city, :province, :postal_code, :phone_number)');
        $this->db->bind('user_id', $data['user_id']);
        $this->db->bind('address_line', $data['address_line']);
        $this->db->bind('city', $data['city']);
        $this->db->bind('province', $data['province']);
        $this->db->bind('postal_code', $data['postal_code']);
        $this->db->bind('phone_number', $data['phone_number']);
        $this->db->execute();
        return $this->db->lastInsertId();
    }
}
