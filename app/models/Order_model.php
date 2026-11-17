<?php

class Order_model {
    private $table = 'orders';
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getOrdersByUserId($user_id) {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE user_id = :user_id ORDER BY created_at DESC');
        $this->db->bind('user_id', $user_id);
        return $this->db->resultSet();
    }

    public function getOrderDetails($order_id) {
        $this->db->query('SELECT od.*, p.name as product_name FROM order_details od JOIN products p ON od.product_id = p.id WHERE od.order_id = :order_id');
        $this->db->bind('order_id', $order_id);
        return $this->db->resultSet();
    }

    public function createOrder($userId, $addressStr, $paymentMethod, $cartItems, $totalAmount) {
        try {
            $this->db->beginTransaction();

            // 1. Create Order
            $this->db->query('INSERT INTO ' . $this->table . ' (user_id, total_amount, status, shipping_address) VALUES (:user_id, :total_amount, :status, :shipping_address)');
            $this->db->bind('user_id', $userId);
            $this->db->bind('total_amount', $totalAmount);
            $this->db->bind('status', 'pending');
            $this->db->bind('shipping_address', $addressStr);
            $this->db->execute();

            $orderId = $this->db->lastInsertId();

            // 2. Create Order Details and Decrease Stock
            foreach ($cartItems as $item) {
                // Insert order detail
                $this->db->query('INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)');
                $this->db->bind('order_id', $orderId);
                $this->db->bind('product_id', $item['id']);
                $this->db->bind('quantity', $item['quantity']);
                $this->db->bind('price', $item['price']);
                $this->db->execute();

                // Decrease stock
                $this->db->query('UPDATE products SET stock = stock - :qty WHERE id = :id AND stock >= :qty');
                $this->db->bind('qty', $item['quantity']);
                $this->db->bind('id', $item['id']);
                $this->db->execute();

                if ($this->db->rowCount() == 0) {
                    // Stock insufficient or product not found
                    throw new Exception("Stok tidak mencukupi untuk produk: " . $item['name']);
                }
            }

            // 3. Create Payment
            $this->db->query('INSERT INTO payments (order_id, amount, payment_method, status) VALUES (:order_id, :amount, :payment_method, :status)');
            $this->db->bind('order_id', $orderId);
            $this->db->bind('amount', $totalAmount);
            $this->db->bind('payment_method', $paymentMethod);
            $this->db->bind('status', 'pending');
            $this->db->execute();

            $this->db->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getOrderByIdAndUser($order_id, $user_id) {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE id = :id AND user_id = :user_id');
        $this->db->bind('id', $order_id);
        $this->db->bind('user_id', $user_id);
        return $this->db->single();
    }

    public function hasPurchasedProduct($user_id, $product_id) {
        $this->db->query('SELECT COUNT(od.id) as count FROM order_details od JOIN ' . $this->table . ' o ON od.order_id = o.id WHERE o.user_id = :user_id AND od.product_id = :product_id AND o.status = :status');
        $this->db->bind('user_id', $user_id);
        $this->db->bind('product_id', $product_id);
        $this->db->bind('status', 'completed');
        $row = $this->db->single();
        return ($row['count'] > 0);
    }

    public function getAdminOrders($limit, $offset, $filters = []) {
        $query = 'SELECT orders.*, users.username, users.email FROM ' . $this->table . ' JOIN users ON orders.user_id = users.id WHERE 1=1';
        
        if (!empty($filters['status'])) {
            $query .= ' AND orders.status = :status';
        }
        if (!empty($filters['start_date'])) {
            $query .= ' AND DATE(orders.created_at) >= :start_date';
        }
        if (!empty($filters['end_date'])) {
            $query .= ' AND DATE(orders.created_at) <= :end_date';
        }
        
        $query .= ' ORDER BY orders.created_at DESC LIMIT :limit OFFSET :offset';
        
        $this->db->query($query);
        
        if (!empty($filters['status'])) {
            $this->db->bind('status', $filters['status']);
        }
        if (!empty($filters['start_date'])) {
            $this->db->bind('start_date', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $this->db->bind('end_date', $filters['end_date']);
        }
        $this->db->bind('limit', $limit);
        $this->db->bind('offset', $offset);
        
        return $this->db->resultSet();
    }

    public function getTotalAdminOrders($filters = []) {
        $query = 'SELECT COUNT(orders.id) as total FROM ' . $this->table . ' WHERE 1=1';
        
        if (!empty($filters['status'])) {
            $query .= ' AND status = :status';
        }
        if (!empty($filters['start_date'])) {
            $query .= ' AND DATE(created_at) >= :start_date';
        }
        if (!empty($filters['end_date'])) {
            $query .= ' AND DATE(created_at) <= :end_date';
        }
        
        $this->db->query($query);
        
        if (!empty($filters['status'])) {
            $this->db->bind('status', $filters['status']);
        }
        if (!empty($filters['start_date'])) {
            $this->db->bind('start_date', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $this->db->bind('end_date', $filters['end_date']);
        }
        
        $row = $this->db->single();
        return $row['total'] ?? 0;
    }

    public function getOrderById($id) {
        $this->db->query('SELECT orders.*, users.username, users.email, users.phone_number FROM ' . $this->table . ' JOIN users ON orders.user_id = users.id WHERE orders.id = :id');
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function getOrderStatusCounts() {
        $this->db->query('SELECT status, COUNT(id) as count FROM ' . $this->table . ' GROUP BY status');
        $results = $this->db->resultSet();
        
        $counts = [
            'pending' => 0,
            'processing' => 0,
            'packed' => 0,
            'shipped' => 0,
            'completed' => 0,
            'cancelled' => 0
        ];

        foreach ($results as $row) {
            $status = strtolower($row['status']);
            if (isset($counts[$status])) {
                $counts[$status] = $row['count'];
            }
        }

        return $counts;
    }

    public function updateOrderStatus($id, $new_status) {
        $this->db->query('UPDATE ' . $this->table . ' SET status = :status WHERE id = :id');
        $this->db->bind('status', $new_status);
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }
}
