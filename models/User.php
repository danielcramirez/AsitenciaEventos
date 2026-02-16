<?php
/**
 * User Model
 */

require_once __DIR__ . '/../config/Database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Authenticate user
     */
    public function authenticate($username, $password) {
        $stmt = $this->db->prepare(
            "SELECT id, username, email, password_hash, full_name, role, active 
             FROM users 
             WHERE username = ? AND active = 1"
        );
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            unset($user['password_hash']);
            return $user;
        }

        return false;
    }

    /**
     * Get user by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare(
            "SELECT id, username, email, full_name, role, active, created_at 
             FROM users 
             WHERE id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Create new user
     */
    public function create($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO users (username, email, password_hash, full_name, role) 
             VALUES (?, ?, ?, ?, ?)"
        );
        
        $password_hash = password_hash($data['password'], PASSWORD_BCRYPT);
        
        return $stmt->execute([
            $data['username'],
            $data['email'],
            $password_hash,
            $data['full_name'],
            $data['role'] ?? 'Asistente'
        ]);
    }

    /**
     * Get all users
     */
    public function getAll() {
        $stmt = $this->db->query(
            "SELECT id, username, email, full_name, role, active, created_at 
             FROM users 
             ORDER BY created_at DESC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Update user
     */
    public function update($id, $data) {
        $sql = "UPDATE users SET username = ?, email = ?, full_name = ?, role = ?";
        $params = [
            $data['username'],
            $data['email'],
            $data['full_name'],
            $data['role']
        ];

        if (!empty($data['password'])) {
            $sql .= ", password_hash = ?";
            $params[] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete user
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Toggle user active status
     */
    public function toggleActive($id) {
        $stmt = $this->db->prepare(
            "UPDATE users SET active = NOT active WHERE id = ?"
        );
        return $stmt->execute([$id]);
    }
}
