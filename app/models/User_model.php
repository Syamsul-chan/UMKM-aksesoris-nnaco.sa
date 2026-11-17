<?php

class User_model {
    private $table = 'users';
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getAllUsers() {
        $this->db->query('SELECT users.*, roles.name as role_name FROM ' . $this->table . ' JOIN roles ON users.role_id = roles.id');
        return $this->db->resultSet();
    }

    public function getUserById($id) {
        $this->db->query('SELECT users.*, roles.name as role_name FROM ' . $this->table . ' JOIN roles ON users.role_id = roles.id WHERE users.id = :id');
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function getUserByEmail($email) {
        $this->db->query('SELECT users.*, roles.name as role_name FROM ' . $this->table . ' JOIN roles ON users.role_id = roles.id WHERE email = :email AND is_active = 1');
        $this->db->bind('email', $email);
        return $this->db->single();
    }

    public function getUserByPhone($phone) {
        $cleanPhone = preg_replace('/[^0-9]/', '', (string)$phone);
        $normalized = $cleanPhone;
        if (str_starts_with($cleanPhone, '0')) {
            $normalized = '62' . substr($cleanPhone, 1);
        } elseif (str_starts_with($cleanPhone, '8')) {
            $normalized = '62' . $cleanPhone;
        }

        $this->db->query('SELECT users.*, roles.name as role_name 
                          FROM ' . $this->table . ' 
                          JOIN roles ON users.role_id = roles.id 
                          WHERE (phone_number = :exact OR phone_number = :normalized OR phone_number = :clean) 
                          AND is_active = 1 
                          LIMIT 1');
        $this->db->bind('exact', $phone);
        $this->db->bind('normalized', $normalized);
        $this->db->bind('clean', $cleanPhone);
        return $this->db->single();
    }

    public function getUserByIdentity($identity) {
        $identity = trim($identity);
        if (empty($identity)) return null;

        $lower = strtolower($identity);

        // 1. Shorthand cerdas untuk akun bawaan sistem
        if ($lower === 'admin') {
            $user = $this->getUserByEmail('admin@nnaco.sa');
            if ($user) return $user;
        } elseif ($lower === 'owner') {
            $user = $this->getUserByEmail('owner@nnaco.sa');
            if ($user) return $user;
        } elseif ($lower === 'operator') {
            $user = $this->getUserByEmail('operator@nnaco.sa');
            if ($user) return $user;
        }

        // 2. Jika mengandung '@', cari via email
        if (str_contains($identity, '@')) {
            $user = $this->getUserByEmail($identity);
            if ($user) return $user;
        }

        // 3. Coba cari via nomor HP jika numerik
        if (preg_match('/^[0-9\+\-\s]{8,20}$/', $identity)) {
            $user = $this->getUserByPhone($identity);
            if ($user) return $user;
        }

        // 4. Coba cari via email persis atau username (case-insensitive)
        $this->db->query('SELECT users.*, roles.name as role_name 
                          FROM ' . $this->table . ' 
                          JOIN roles ON users.role_id = roles.id 
                          WHERE (email = :identity OR LOWER(username) = :lower_id OR username = :identity) 
                          AND is_active = 1 LIMIT 1');
        $this->db->bind('identity', $identity);
        $this->db->bind('lower_id', $lower);
        return $this->db->single();
    }

    public function createUser($data) {
        $query = "INSERT INTO users (role_id, username, email, phone_number, password, is_active) VALUES (:role_id, :username, :email, :phone_number, :password, :is_active)";
        $this->db->query($query);
        $this->db->bind('role_id', $data['role_id'] ?? 1);
        $this->db->bind('username', $data['username']);
        $this->db->bind('email', $data['email']);
        $this->db->bind('phone_number', $data['phone_number'] ?? null);
        $this->db->bind('password', password_hash($data['password'], PASSWORD_BCRYPT));
        $this->db->bind('is_active', $data['is_active'] ?? 1);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function registerCustomerByPhone($normalizedPhone) {
        $cleanPhone = preg_replace('/[^0-9]/', '', (string)$normalizedPhone);
        $suffix = substr($cleanPhone, -4);
        $username = 'Customer_' . $suffix;
        $email = $cleanPhone . '@wa.nnaco.sa';
        $randomPass = bin2hex(random_bytes(8));

        $query = "INSERT INTO users (role_id, username, email, phone_number, password, is_active) 
                  VALUES (1, :username, :email, :phone_number, :password, 1)";
        $this->db->query($query);
        $this->db->bind('username', $username);
        $this->db->bind('email', $email);
        $this->db->bind('phone_number', $cleanPhone);
        $this->db->bind('password', password_hash($randomPass, PASSWORD_BCRYPT));
        $this->db->execute();

        $newId = $this->db->lastInsertId();
        return $this->getUserById($newId);
    }

    public function getCustomers($limit = 10, $offset = 0, $filters = []) {
        if (!is_array($filters)) {
            $filters = ['q' => $filters];
        }

        $query = 'SELECT users.id, users.role_id, users.username, users.email, users.phone_number, users.is_active, users.created_at, users.updated_at, roles.name as role_name,
                         (SELECT COUNT(o.id) FROM orders o WHERE o.user_id = users.id) as total_orders,
                         (SELECT COALESCE(SUM(o.total_amount), 0) FROM orders o WHERE o.user_id = users.id AND o.status != "cancelled") as total_spent,
                         (SELECT o.created_at FROM orders o WHERE o.user_id = users.id ORDER BY o.created_at DESC LIMIT 1) as last_order_date
                  FROM ' . $this->table . ' 
                  JOIN roles ON users.role_id = roles.id 
                  WHERE roles.name = "Customer"';
        
        $params = [];

        // 1. Search Query (username, email, phone_number)
        if (!empty($filters['q'])) {
            $query .= ' AND (users.username LIKE :search OR users.email LIKE :search OR users.phone_number LIKE :search)';
            $params['search'] = '%' . trim($filters['q']) . '%';
        }

        // 2. Status Filter
        if (isset($filters['status']) && $filters['status'] !== '' && $filters['status'] !== 'all') {
            if ($filters['status'] === 'active' || $filters['status'] === '1') {
                $query .= ' AND users.is_active = 1';
            } elseif ($filters['status'] === 'inactive' || $filters['status'] === 'blocked' || $filters['status'] === '0') {
                $query .= ' AND users.is_active = 0';
            }
        }

        // 3. Orders Filter (using HAVING or subquery conditions)
        if (!empty($filters['order_filter']) && $filters['order_filter'] !== 'all') {
            if ($filters['order_filter'] === 'has_orders') {
                $query .= ' AND (SELECT COUNT(o.id) FROM orders o WHERE o.user_id = users.id) > 0';
            } elseif ($filters['order_filter'] === 'no_orders') {
                $query .= ' AND (SELECT COUNT(o.id) FROM orders o WHERE o.user_id = users.id) = 0';
            }
        }

        // 4. Sorting
        $sort = $filters['sort'] ?? 'newest';
        switch ($sort) {
            case 'oldest':
                $query .= ' ORDER BY users.created_at ASC';
                break;
            case 'name_asc':
                $query .= ' ORDER BY users.username ASC';
                break;
            case 'name_desc':
                $query .= ' ORDER BY users.username DESC';
                break;
            case 'orders_desc':
                $query .= ' ORDER BY total_orders DESC, users.created_at DESC';
                break;
            case 'spent_desc':
                $query .= ' ORDER BY total_spent DESC, users.created_at DESC';
                break;
            case 'newest':
            default:
                $query .= ' ORDER BY users.created_at DESC';
                break;
        }

        $query .= ' LIMIT :limit OFFSET :offset';

        $this->db->query($query);
        foreach ($params as $k => $v) {
            $this->db->bind($k, $v);
        }
        $this->db->bind('limit', (int)$limit);
        $this->db->bind('offset', (int)$offset);

        return $this->db->resultSet();
    }

    public function getTotalCustomers($filters = []) {
        if (!is_array($filters)) {
            $filters = ['q' => $filters];
        }

        $query = 'SELECT COUNT(users.id) as total 
                  FROM ' . $this->table . ' 
                  JOIN roles ON users.role_id = roles.id 
                  WHERE roles.name = "Customer"';
        
        $params = [];

        if (!empty($filters['q'])) {
            $query .= ' AND (users.username LIKE :search OR users.email LIKE :search OR users.phone_number LIKE :search)';
            $params['search'] = '%' . trim($filters['q']) . '%';
        }

        if (isset($filters['status']) && $filters['status'] !== '' && $filters['status'] !== 'all') {
            if ($filters['status'] === 'active' || $filters['status'] === '1') {
                $query .= ' AND users.is_active = 1';
            } elseif ($filters['status'] === 'inactive' || $filters['status'] === 'blocked' || $filters['status'] === '0') {
                $query .= ' AND users.is_active = 0';
            }
        }

        if (!empty($filters['order_filter']) && $filters['order_filter'] !== 'all') {
            if ($filters['order_filter'] === 'has_orders') {
                $query .= ' AND (SELECT COUNT(o.id) FROM orders o WHERE o.user_id = users.id) > 0';
            } elseif ($filters['order_filter'] === 'no_orders') {
                $query .= ' AND (SELECT COUNT(o.id) FROM orders o WHERE o.user_id = users.id) = 0';
            }
        }

        $this->db->query($query);
        foreach ($params as $k => $v) {
            $this->db->bind($k, $v);
        }

        $row = $this->db->single();
        return (int)($row['total'] ?? 0);
    }

    public function getCustomerStatsSummary() {
        $stats = [
            'total' => 0,
            'active' => 0,
            'blocked' => 0,
            'with_orders' => 0,
            'total_spent' => 0,
            'new_this_month' => 0
        ];

        // Total, Active, Blocked, New this month
        $this->db->query('SELECT 
            COUNT(id) as total,
            SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as blocked,
            SUM(CASE WHEN MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE()) THEN 1 ELSE 0 END) as new_this_month
            FROM users WHERE role_id = 1');
        $counts = $this->db->single();
        if ($counts) {
            $stats['total'] = (int)($counts['total'] ?? 0);
            $stats['active'] = (int)($counts['active'] ?? 0);
            $stats['blocked'] = (int)($counts['blocked'] ?? 0);
            $stats['new_this_month'] = (int)($counts['new_this_month'] ?? 0);
        }

        // Customers with orders and total spent
        $this->db->query('SELECT 
            COUNT(DISTINCT user_id) as with_orders,
            COALESCE(SUM(total_amount), 0) as total_spent
            FROM orders WHERE user_id IN (SELECT id FROM users WHERE role_id = 1) AND status != "cancelled"');
        $orderCounts = $this->db->single();
        if ($orderCounts) {
            $stats['with_orders'] = (int)($orderCounts['with_orders'] ?? 0);
            $stats['total_spent'] = (float)($orderCounts['total_spent'] ?? 0);
        }

        return $stats;
    }

    public function getCustomerDetails($id) {
        $customer = $this->getUserById($id);
        if (!$customer) {
            return null;
        }

        // Get Addresses
        $this->db->query('SELECT * FROM addresses WHERE user_id = :id ORDER BY id DESC');
        $this->db->bind('id', $id);
        $customer['addresses'] = $this->db->resultSet();

        // Get Orders
        $this->db->query('SELECT id, total_amount, status, shipping_address, created_at FROM orders WHERE user_id = :id ORDER BY created_at DESC LIMIT 10');
        $this->db->bind('id', $id);
        $customer['orders'] = $this->db->resultSet();

        // Calculate summary
        $this->db->query('SELECT COUNT(id) as total_orders, COALESCE(SUM(total_amount), 0) as total_spent FROM orders WHERE user_id = :id AND status != "cancelled"');
        $this->db->bind('id', $id);
        $summary = $this->db->single();
        $customer['total_orders'] = (int)($summary['total_orders'] ?? 0);
        $customer['total_spent'] = (float)($summary['total_spent'] ?? 0);

        return $customer;
    }

    public function toggleUserStatus($id, $status) {
        $this->db->query('UPDATE ' . $this->table . ' SET is_active = :status WHERE id = :id');
        $this->db->bind('status', (int)$status);
        $this->db->bind('id', (int)$id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function resetPassword($id, $newPassword) {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $this->db->query('UPDATE ' . $this->table . ' SET password = :password WHERE id = :id');
        $this->db->bind('password', $hash);
        $this->db->bind('id', (int)$id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function deleteCustomer($id) {
        // 1. Check if user has orders
        $this->db->query('SELECT COUNT(id) as total FROM orders WHERE user_id = :id');
        $this->db->bind('id', $id);
        $check = $this->db->single();
        $orderCount = (int)($check['total'] ?? 0);

        if ($orderCount > 0) {
            return [
                'success' => false,
                'has_orders' => true,
                'order_count' => $orderCount,
                'message' => "Customer ini memiliki {$orderCount} riwayat pesanan. Demi keamanan laporan transaksi keuangan, akun ini tidak boleh dihapus, tetapi Anda dapat menonaktifkan/memblokir akun ini."
            ];
        }

        try {
            // Delete secondary records (addresses, otp_logs, reviews)
            $this->db->query('DELETE FROM addresses WHERE user_id = :id');
            $this->db->bind('id', $id);
            $this->db->execute();

            $this->db->query('DELETE FROM otp_logs WHERE user_id = :id');
            $this->db->bind('id', $id);
            $this->db->execute();

            $this->db->query('DELETE FROM reviews WHERE user_id = :id');
            $this->db->bind('id', $id);
            $this->db->execute();

            // Delete user
            $this->db->query('DELETE FROM ' . $this->table . ' WHERE id = :id AND role_id = 1');
            $this->db->bind('id', $id);
            $this->db->execute();

            return [
                'success' => true,
                'has_orders' => false,
                'message' => 'Akun customer berhasil dihapus secara permanen.'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'has_orders' => false,
                'message' => 'Gagal menghapus customer: ' . $e->getMessage()
            ];
        }
    }
}

