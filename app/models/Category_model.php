<?php

class Category_model {
    private $table = 'categories';
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getAllCategories() {
        $this->db->query('SELECT * FROM ' . $this->table);
        return $this->db->resultSet();
    }

    public function getCategoryById($id) {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE id = :id');
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function getAdminCategories($limit, $offset, $search = '') {
        $query = 'SELECT * FROM ' . $this->table;
        
        if (!empty($search)) {
            $query .= ' WHERE name LIKE :search';
        }
        
        $query .= ' ORDER BY created_at DESC LIMIT :limit OFFSET :offset';
        
        $this->db->query($query);
        
        if (!empty($search)) {
            $this->db->bind('search', '%' . $search . '%');
        }
        $this->db->bind('limit', $limit);
        $this->db->bind('offset', $offset);
        
        return $this->db->resultSet();
    }

    public function getTotalAdminCategories($search = '') {
        $query = 'SELECT COUNT(id) as total FROM ' . $this->table;
        
        if (!empty($search)) {
            $query .= ' WHERE name LIKE :search';
        }
        
        $this->db->query($query);
        
        if (!empty($search)) {
            $this->db->bind('search', '%' . $search . '%');
        }
        
        $row = $this->db->single();
        return $row['total'] ?? 0;
    }

    public function createCategory($data) {
        $this->db->query('INSERT INTO ' . $this->table . ' (name, slug) VALUES (:name, :slug)');
        $this->db->bind('name', $data['name']);
        
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['name'])));
        $this->db->bind('slug', $slug);
        
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function updateCategory($id, $data) {
        $this->db->query('UPDATE ' . $this->table . ' SET name = :name, slug = :slug WHERE id = :id');
        $this->db->bind('name', $data['name']);
        
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['name'])));
        $this->db->bind('slug', $slug);
        $this->db->bind('id', $id);
        
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function deleteCategory($id) {
        try {
            $this->db->query('DELETE FROM ' . $this->table . ' WHERE id = :id');
            $this->db->bind('id', $id);
            $this->db->execute();
            return true;
        } catch (Exception $e) {
            // Cannot delete because it's used in products
            return false;
        }
    }
}
