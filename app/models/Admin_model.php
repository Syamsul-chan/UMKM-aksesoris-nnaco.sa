<?php

class Admin_model {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getDashboardStats() {
        $stats = [];

        // Total Produk
        $this->db->query('SELECT COUNT(id) as total FROM products');
        $stats['total_products'] = $this->db->single()['total'];

        // Total Kategori
        $this->db->query('SELECT COUNT(id) as total FROM categories');
        $stats['total_categories'] = $this->db->single()['total'];

        // Total Customer (role_id = 1, assuming 1 is Customer based on seeder)
        $this->db->query('SELECT COUNT(id) as total FROM users WHERE role_id = (SELECT id FROM roles WHERE name = "Customer" LIMIT 1)');
        $stats['total_customers'] = $this->db->single()['total'];

        // Total Order (yang completed)
        $this->db->query('SELECT COUNT(id) as total FROM orders WHERE status = "completed"');
        $stats['total_orders'] = $this->db->single()['total'];

        // Total Pendapatan
        $this->db->query('SELECT SUM(total_amount) as total FROM orders WHERE status = "completed"');
        $row = $this->db->single();
        $stats['total_revenue'] = $row['total'] ?? 0;

        // Produk stok menipis (<= 5)
        $this->db->query('SELECT * FROM products WHERE stock <= 5 AND is_active = 1 ORDER BY stock ASC LIMIT 5');
        $stats['low_stock_products'] = $this->db->resultSet();

        // Produk Unggulan / Terkini
        $this->db->query('SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1 ORDER BY p.id DESC LIMIT 6');
        $stats['featured_products'] = $this->db->resultSet();

        return $stats;
    }
}
