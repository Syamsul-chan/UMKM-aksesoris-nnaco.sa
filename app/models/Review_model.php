<?php

class Review_model {
    private $table = 'reviews';
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getReviewsByProductId($product_id) {
        $this->db->query('SELECT r.*, u.username FROM ' . $this->table . ' r JOIN users u ON r.user_id = u.id WHERE r.product_id = :product_id ORDER BY r.created_at DESC');
        $this->db->bind('product_id', $product_id);
        return $this->db->resultSet();
    }

    public function getAverageRating($product_id) {
        $this->db->query('SELECT AVG(rating) as avg_rating, COUNT(id) as total_reviews FROM ' . $this->table . ' WHERE product_id = :product_id');
        $this->db->bind('product_id', $product_id);
        $result = $this->db->single();
        return [
            'rating' => isset($result['avg_rating']) ? round($result['avg_rating'], 1) : 0,
            'count' => $result['total_reviews'] ?? 0
        ];
    }

    public function hasReviewedProduct($user_id, $product_id) {
        $this->db->query('SELECT id FROM ' . $this->table . ' WHERE user_id = :user_id AND product_id = :product_id');
        $this->db->bind('user_id', $user_id);
        $this->db->bind('product_id', $product_id);
        $this->db->execute();
        return ($this->db->rowCount() > 0);
    }

    public function addReview($data) {
        $this->db->query('INSERT INTO ' . $this->table . ' (product_id, user_id, rating, comment) VALUES (:product_id, :user_id, :rating, :comment)');
        $this->db->bind('product_id', $data['product_id']);
        $this->db->bind('user_id', $data['user_id']);
        $this->db->bind('rating', $data['rating']);
        $this->db->bind('comment', $data['comment']);
        $this->db->execute();
        return $this->db->rowCount();
    }
}
