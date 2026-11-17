<?php

class Role_model {
    private $table = 'roles';
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getAllRoles() {
        $this->db->query('SELECT * FROM ' . $this->table);
        return $this->db->resultSet();
    }

    public function getRoleById($id) {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE id = :id');
        $this->db->bind('id', $id);
        return $this->db->single();
    }
}
