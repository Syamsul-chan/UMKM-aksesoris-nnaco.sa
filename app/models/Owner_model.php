<?php

class Owner_model {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    private function bindDateRange($start, $end) {
        if ($start) $this->db->bind('start_date', $start . ' 00:00:00');
        if ($end) $this->db->bind('end_date', $end . ' 23:59:59');
    }

    private function getDateWhereClause($start, $end, $prefix = 'created_at') {
        $where = '';
        if ($start && $end) {
            $where = " AND $prefix >= :start_date AND $prefix <= :end_date";
        } elseif ($start) {
            $where = " AND $prefix >= :start_date";
        } elseif ($end) {
            $where = " AND $prefix <= :end_date";
        }
        return $where;
    }

    public function getOverviewStats($start = null, $end = null) {
        $stats = [];
        
        // Total Revenue (Only completed orders)
        $q_rev = 'SELECT SUM(total_amount) as total FROM orders WHERE status = "completed"';
        $q_rev .= $this->getDateWhereClause($start, $end);
        $this->db->query($q_rev);
        $this->bindDateRange($start, $end);
        $stats['total_revenue'] = $this->db->single()['total'] ?? 0;

        // Total Orders (All statuses, or just completed? Usually all)
        $q_ord = 'SELECT COUNT(id) as total FROM orders WHERE 1=1';
        $q_ord .= $this->getDateWhereClause($start, $end);
        $this->db->query($q_ord);
        $this->bindDateRange($start, $end);
        $stats['total_orders'] = $this->db->single()['total'] ?? 0;

        // Total Customers
        $q_cust = 'SELECT COUNT(users.id) as total FROM users JOIN roles ON users.role_id = roles.id WHERE roles.name = "Customer"';
        $q_cust .= $this->getDateWhereClause($start, $end, 'users.created_at');
        $this->db->query($q_cust);
        $this->bindDateRange($start, $end);
        $stats['total_customers'] = $this->db->single()['total'] ?? 0;

        // Average Order Value (AOV)
        // Calculated by Total Revenue / Total Completed Orders
        $q_comp_ord = 'SELECT COUNT(id) as total FROM orders WHERE status = "completed"';
        $q_comp_ord .= $this->getDateWhereClause($start, $end);
        $this->db->query($q_comp_ord);
        $this->bindDateRange($start, $end);
        $total_completed = $this->db->single()['total'] ?? 0;

        $stats['aov'] = $total_completed > 0 ? ($stats['total_revenue'] / $total_completed) : 0;

        return $stats;
    }

    public function getTopProducts($start = null, $end = null, $limit = 5) {
        $query = 'SELECT p.name, SUM(od.quantity) as total_sold, SUM(od.quantity * od.price) as revenue 
                  FROM order_details od 
                  JOIN orders o ON od.order_id = o.id 
                  JOIN products p ON od.product_id = p.id 
                  WHERE o.status = "completed"';
        
        $query .= $this->getDateWhereClause($start, $end, 'o.created_at');
        $query .= ' GROUP BY p.id ORDER BY total_sold DESC LIMIT :limit';

        $this->db->query($query);
        $this->bindDateRange($start, $end);
        $this->db->bind('limit', $limit);

        $results = $this->db->resultSet();
        if (empty($results)) {
            $this->db->query('SELECT p.id, p.name, p.price, p.stock, 0 as total_sold, 0 as revenue, c.name as category_name 
                              FROM products p 
                              LEFT JOIN categories c ON p.category_id = c.id 
                              WHERE p.is_active = 1 
                              ORDER BY p.id ASC LIMIT :limit');
            $this->db->bind('limit', $limit);
            $results = $this->db->resultSet();
        }
        return $results;
    }

    public function getTopCategories($start = null, $end = null, $limit = 5) {
        $query = 'SELECT c.name, SUM(od.quantity) as total_sold 
                  FROM order_details od 
                  JOIN orders o ON od.order_id = o.id 
                  JOIN products p ON od.product_id = p.id 
                  JOIN categories c ON p.category_id = c.id 
                  WHERE o.status = "completed"';
        
        $query .= $this->getDateWhereClause($start, $end, 'o.created_at');
        $query .= ' GROUP BY c.id ORDER BY total_sold DESC LIMIT :limit';

        $this->db->query($query);
        $this->bindDateRange($start, $end);
        $this->db->bind('limit', $limit);

        return $this->db->resultSet();
    }

    public function getOrdersByStatus($start = null, $end = null) {
        $query = 'SELECT status, COUNT(id) as count FROM orders WHERE 1=1';
        $query .= $this->getDateWhereClause($start, $end);
        $query .= ' GROUP BY status';

        $this->db->query($query);
        $this->bindDateRange($start, $end);

        $results = $this->db->resultSet();
        $counts = [
            'pending' => 0, 'processing' => 0, 'packed' => 0, 
            'shipped' => 0, 'completed' => 0, 'cancelled' => 0
        ];
        foreach ($results as $row) {
            $counts[strtolower($row['status'])] = $row['count'];
        }
        return $counts;
    }

    public function getSalesByPeriod($start = null, $end = null) {
        // Group by DATE for simple charts
        $query = 'SELECT DATE(created_at) as sale_date, SUM(total_amount) as revenue 
                  FROM orders 
                  WHERE status = "completed"';
        $query .= $this->getDateWhereClause($start, $end);
        $query .= ' GROUP BY DATE(created_at) ORDER BY DATE(created_at) ASC';

        $this->db->query($query);
        $this->bindDateRange($start, $end);
        
        return $this->db->resultSet();
    }

    public function getSalesReport($start = null, $end = null, $limit = 20, $offset = 0) {
        $query = 'SELECT orders.*, users.username FROM orders 
                  JOIN users ON orders.user_id = users.id 
                  WHERE orders.status = "completed"';
        $query .= $this->getDateWhereClause($start, $end, 'orders.created_at');
        $query .= ' ORDER BY orders.created_at DESC LIMIT :limit OFFSET :offset';

        $this->db->query($query);
        $this->bindDateRange($start, $end);
        $this->db->bind('limit', $limit);
        $this->db->bind('offset', $offset);

        return $this->db->resultSet();
    }

    public function getTotalSalesReport($start = null, $end = null) {
        $query = 'SELECT COUNT(id) as total FROM orders WHERE status = "completed"';
        $query .= $this->getDateWhereClause($start, $end);

        $this->db->query($query);
        $this->bindDateRange($start, $end);
        return $this->db->single()['total'] ?? 0;
    }

    public function getStockReport($limit = 50, $offset = 0) {
        $query = 'SELECT p.id, p.name, c.name as category_name, p.stock, p.is_active 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  ORDER BY p.stock ASC LIMIT :limit OFFSET :offset';
        $this->db->query($query);
        $this->db->bind('limit', $limit);
        $this->db->bind('offset', $offset);
        return $this->db->resultSet();
    }

    public function getTotalStockReport() {
        $this->db->query('SELECT COUNT(id) as total FROM products');
        return $this->db->single()['total'] ?? 0;
    }
}
