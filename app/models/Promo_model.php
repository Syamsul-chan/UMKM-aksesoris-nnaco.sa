<?php

class Promo_model {
    private $table = 'promos';
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getActivePromos() {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE is_active = 1 AND start_date <= NOW() AND end_date >= NOW() ORDER BY id DESC');
        return $this->db->resultSet();
    }

    public function getAllPromos() {
        $this->db->query('SELECT * FROM ' . $this->table . ' ORDER BY id DESC');
        return $this->db->resultSet();
    }

    public function getPromoById($id) {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE id = :id');
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function getPromoByCode($code) {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE code = :code AND is_active = 1 AND start_date <= NOW() AND end_date >= NOW() LIMIT 1');
        $this->db->bind('code', strtoupper(trim($code)));
        return $this->db->single();
    }

    public function createPromo($data) {
        $this->db->query('INSERT INTO ' . $this->table . ' (name, code, discount_type, discount_value, start_date, end_date, is_active) 
                          VALUES (:name, :code, :discount_type, :discount_value, :start_date, :end_date, :is_active)');
        $this->db->bind('name', $data['name']);
        $this->db->bind('code', strtoupper(trim($data['code'])));
        $this->db->bind('discount_type', $data['discount_type']);
        $this->db->bind('discount_value', $data['discount_value']);
        $this->db->bind('start_date', $data['start_date']);
        $this->db->bind('end_date', $data['end_date']);
        $this->db->bind('is_active', $data['is_active'] ?? 1);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function updatePromo($id, $data) {
        $this->db->query('UPDATE ' . $this->table . ' SET name = :name, code = :code, discount_type = :discount_type, discount_value = :discount_value, start_date = :start_date, end_date = :end_date, is_active = :is_active WHERE id = :id');
        $this->db->bind('name', $data['name']);
        $this->db->bind('code', strtoupper(trim($data['code'])));
        $this->db->bind('discount_type', $data['discount_type']);
        $this->db->bind('discount_value', $data['discount_value']);
        $this->db->bind('start_date', $data['start_date']);
        $this->db->bind('end_date', $data['end_date']);
        $this->db->bind('is_active', $data['is_active']);
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function togglePromo($id) {
        $promo = $this->getPromoById($id);
        if (!$promo) return 0;
        $newStatus = $promo['is_active'] ? 0 : 1;
        $this->db->query('UPDATE ' . $this->table . ' SET is_active = :status WHERE id = :id');
        $this->db->bind('status', $newStatus);
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function deletePromo($id) {
        $this->db->query('DELETE FROM ' . $this->table . ' WHERE id = :id');
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }
}
