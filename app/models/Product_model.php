<?php

class Product_model {
    private $table = 'products';
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getAllProducts() {
        $this->db->query('SELECT products.*, categories.name as category_name FROM ' . $this->table . ' JOIN categories ON products.category_id = categories.id WHERE is_active = 1');
        return $this->db->resultSet();
    }

    public function getProductById($id) {
        $this->db->query('SELECT products.*, categories.name as category_name FROM ' . $this->table . ' JOIN categories ON products.category_id = categories.id WHERE products.id = :id AND products.is_active = 1');
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    private function buildFilterQuery($filters) {
        $query = " WHERE products.is_active = 1";
        $params = [];

        if (!empty($filters['q'])) {
            $query .= " AND (products.name LIKE :q OR products.description LIKE :q)";
            $params['q'] = '%' . $filters['q'] . '%';
        }

        if (!empty($filters['cat'])) {
            $query .= " AND products.category_id = :cat";
            $params['cat'] = $filters['cat'];
        }

        if (!empty($filters['min'])) {
            $query .= " AND products.price >= :min";
            $params['min'] = $filters['min'];
        }

        if (!empty($filters['max'])) {
            $query .= " AND products.price <= :max";
            $params['max'] = $filters['max'];
        }

        return ['query' => $query, 'params' => $params];
    }

    public function getPaginatedProducts($limit, $offset, $filters = []) {
        $filterData = $this->buildFilterQuery($filters);
        
        $sql = 'SELECT products.*, categories.name as category_name FROM ' . $this->table . ' JOIN categories ON products.category_id = categories.id ' . $filterData['query'] . ' ORDER BY products.created_at DESC LIMIT :limit OFFSET :offset';
        
        $this->db->query($sql);
        
        foreach ($filterData['params'] as $key => $value) {
            $this->db->bind($key, $value);
        }
        
        $this->db->bind('limit', $limit);
        $this->db->bind('offset', $offset);

        return $this->db->resultSet();
    }

    public function getTotalProducts($filters = []) {
        $filterData = $this->buildFilterQuery($filters);
        
        $sql = 'SELECT COUNT(products.id) as total FROM ' . $this->table . ' ' . $filterData['query'];
        $this->db->query($sql);
        
        foreach ($filterData['params'] as $key => $value) {
            $this->db->bind($key, $value);
        }
        
        $row = $this->db->single();
        return $row['total'] ?? 0;
    }

    public function decreaseStock($id, $qty) {
        $this->db->query('UPDATE ' . $this->table . ' SET stock = stock - :qty WHERE id = :id AND stock >= :qty');
        $this->db->bind('qty', $qty);
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function increaseStock($id, $qty) {
        $this->db->query('UPDATE ' . $this->table . ' SET stock = stock + :qty WHERE id = :id');
        $this->db->bind('qty', $qty);
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function getAdminProducts($limit, $offset, $filters = []) {
        if (!is_array($filters)) {
            $filters = ['q' => $filters];
        }

        $query = 'SELECT products.*, categories.name as category_name 
                  FROM ' . $this->table . ' 
                  LEFT JOIN categories ON products.category_id = categories.id 
                  WHERE 1=1';
        $params = [];

        // 1. Search Query
        if (!empty($filters['q'])) {
            $query .= ' AND (products.name LIKE :search OR products.description LIKE :search OR categories.name LIKE :search)';
            $params['search'] = '%' . trim($filters['q']) . '%';
        }

        // 2. Category Filter
        if (!empty($filters['category_id']) && is_numeric($filters['category_id'])) {
            $query .= ' AND products.category_id = :category_id';
            $params['category_id'] = (int)$filters['category_id'];
        }

        // 3. Status Filter (active / inactive)
        if (isset($filters['status']) && $filters['status'] !== '' && $filters['status'] !== 'all') {
            if ($filters['status'] === 'active' || $filters['status'] === '1') {
                $query .= ' AND products.is_active = 1';
            } elseif ($filters['status'] === 'inactive' || $filters['status'] === '0') {
                $query .= ' AND products.is_active = 0';
            }
        }

        // 4. Stock Status Filter
        if (!empty($filters['stock_status']) && $filters['stock_status'] !== 'all') {
            if ($filters['stock_status'] === 'empty') {
                $query .= ' AND products.stock <= 0';
            } elseif ($filters['stock_status'] === 'low') {
                $query .= ' AND products.stock > 0 AND products.stock <= 5';
            } elseif ($filters['stock_status'] === 'available') {
                $query .= ' AND products.stock > 0';
            }
        }

        // 5. Promo Filter
        if (!empty($filters['is_promo'])) {
            $query .= ' AND products.is_promo = 1 AND products.discount_percent > 0';
        }

        // 6. Sorting
        $sort = $filters['sort'] ?? 'newest';
        switch ($sort) {
            case 'oldest':
                $query .= ' ORDER BY products.created_at ASC';
                break;
            case 'price_asc':
                $query .= ' ORDER BY products.price ASC';
                break;
            case 'price_desc':
                $query .= ' ORDER BY products.price DESC';
                break;
            case 'name_asc':
                $query .= ' ORDER BY products.name ASC';
                break;
            case 'name_desc':
                $query .= ' ORDER BY products.name DESC';
                break;
            case 'stock_desc':
                $query .= ' ORDER BY products.stock DESC';
                break;
            case 'stock_asc':
                $query .= ' ORDER BY products.stock ASC';
                break;
            case 'newest':
            default:
                $query .= ' ORDER BY products.created_at DESC';
                break;
        }

        $query .= ' LIMIT :limit OFFSET :offset';

        $this->db->query($query);
        foreach ($params as $key => $val) {
            $this->db->bind($key, $val);
        }
        $this->db->bind('limit', (int)$limit);
        $this->db->bind('offset', (int)$offset);

        return $this->db->resultSet();
    }

    public function getTotalAdminProducts($filters = []) {
        if (!is_array($filters)) {
            $filters = ['q' => $filters];
        }

        $query = 'SELECT COUNT(products.id) as total 
                  FROM ' . $this->table . ' 
                  LEFT JOIN categories ON products.category_id = categories.id 
                  WHERE 1=1';
        $params = [];

        if (!empty($filters['q'])) {
            $query .= ' AND (products.name LIKE :search OR products.description LIKE :search OR categories.name LIKE :search)';
            $params['search'] = '%' . trim($filters['q']) . '%';
        }

        if (!empty($filters['category_id']) && is_numeric($filters['category_id'])) {
            $query .= ' AND products.category_id = :category_id';
            $params['category_id'] = (int)$filters['category_id'];
        }

        if (isset($filters['status']) && $filters['status'] !== '' && $filters['status'] !== 'all') {
            if ($filters['status'] === 'active' || $filters['status'] === '1') {
                $query .= ' AND products.is_active = 1';
            } elseif ($filters['status'] === 'inactive' || $filters['status'] === '0') {
                $query .= ' AND products.is_active = 0';
            }
        }

        if (!empty($filters['stock_status']) && $filters['stock_status'] !== 'all') {
            if ($filters['stock_status'] === 'empty') {
                $query .= ' AND products.stock <= 0';
            } elseif ($filters['stock_status'] === 'low') {
                $query .= ' AND products.stock > 0 AND products.stock <= 5';
            } elseif ($filters['stock_status'] === 'available') {
                $query .= ' AND products.stock > 0';
            }
        }

        if (!empty($filters['is_promo'])) {
            $query .= ' AND products.is_promo = 1 AND products.discount_percent > 0';
        }

        $this->db->query($query);
        foreach ($params as $key => $val) {
            $this->db->bind($key, $val);
        }

        $row = $this->db->single();
        return (int)($row['total'] ?? 0);
    }

    public function createProduct($data) {
        $discount = isset($data['discount_percent']) ? (int)$data['discount_percent'] : 0;
        $isPromo = isset($data['is_promo']) ? (int)$data['is_promo'] : 0;
        $promoPrice = ($isPromo && $discount > 0) ? round($data['price'] * (1 - ($discount / 100)), 2) : null;

        $this->db->query('INSERT INTO ' . $this->table . ' (category_id, name, description, price, discount_percent, promo_price, is_promo, stock, image, is_active) VALUES (:category_id, :name, :description, :price, :discount_percent, :promo_price, :is_promo, :stock, :image, :is_active)');
        $this->db->bind('category_id', $data['category_id']);
        $this->db->bind('name', $data['name']);
        $this->db->bind('description', $data['description']);
        $this->db->bind('price', $data['price']);
        $this->db->bind('discount_percent', $discount);
        $this->db->bind('promo_price', $promoPrice);
        $this->db->bind('is_promo', $isPromo);
        $this->db->bind('stock', $data['stock']);
        $this->db->bind('image', $data['image']);
        $this->db->bind('is_active', $data['is_active']);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function updateProduct($id, $data) {
        $discount = isset($data['discount_percent']) ? (int)$data['discount_percent'] : 0;
        $isPromo = isset($data['is_promo']) ? (int)$data['is_promo'] : 0;
        $promoPrice = ($isPromo && $discount > 0) ? round($data['price'] * (1 - ($discount / 100)), 2) : null;

        $query = 'UPDATE ' . $this->table . ' SET category_id = :category_id, name = :name, description = :description, price = :price, discount_percent = :discount_percent, promo_price = :promo_price, is_promo = :is_promo, stock = :stock, is_active = :is_active';
        
        if (isset($data['image']) && !empty($data['image'])) {
            $query .= ', image = :image';
        }
        
        $query .= ' WHERE id = :id';
        
        $this->db->query($query);
        $this->db->bind('category_id', $data['category_id']);
        $this->db->bind('name', $data['name']);
        $this->db->bind('description', $data['description']);
        $this->db->bind('price', $data['price']);
        $this->db->bind('discount_percent', $discount);
        $this->db->bind('promo_price', $promoPrice);
        $this->db->bind('is_promo', $isPromo);
        $this->db->bind('stock', $data['stock']);
        $this->db->bind('is_active', $data['is_active']);
        $this->db->bind('id', $id);
        
        if (isset($data['image']) && !empty($data['image'])) {
            $this->db->bind('image', $data['image']);
        }
        
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function setProductPromo($id, $discountPercent) {
        $product = $this->getProductById($id);
        if (!$product) return 0;

        $discount = max(0, min(100, (int)$discountPercent));
        $isPromo = ($discount > 0) ? 1 : 0;
        $promoPrice = ($discount > 0) ? round($product['price'] * (1 - ($discount / 100)), 2) : null;

        $this->db->query('UPDATE ' . $this->table . ' SET discount_percent = :discount, promo_price = :promo_price, is_promo = :is_promo WHERE id = :id');
        $this->db->bind('discount', $discount);
        $this->db->bind('promo_price', $promoPrice);
        $this->db->bind('is_promo', $isPromo);
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function removeProductPromo($id) {
        $this->db->query('UPDATE ' . $this->table . ' SET discount_percent = 0, promo_price = NULL, is_promo = 0 WHERE id = :id');
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function getPromoProducts() {
        $this->db->query('SELECT products.*, categories.name as category_name FROM ' . $this->table . ' JOIN categories ON products.category_id = categories.id WHERE products.is_active = 1 AND products.is_promo = 1 AND products.discount_percent > 0 ORDER BY products.discount_percent DESC');
        return $this->db->resultSet();
    }

    public function toggleStatus($id, $status) {
        $this->db->query('UPDATE ' . $this->table . ' SET is_active = :status WHERE id = :id');
        $this->db->bind('status', $status);
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }
}
