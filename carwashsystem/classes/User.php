<?php
// User class for authentication and user management
require_once __DIR__ . '/Database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Authenticate user
    public function authenticate($username, $password) {
        $user = $this->db->fetch(
            "SELECT id, username, password, role, first_name, last_name FROM users WHERE username = ?",
            [$username]
        );

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    // Get user by ID
    public function getById($id) {
        return $this->db->fetch(
            "SELECT id, username, role, first_name, last_name, email, phone FROM users WHERE id = ?",
            [$id]
        );
    }

    // Get all users
    public function getAll() {
        return $this->db->fetchAll("SELECT id, username, role, first_name, last_name, email, phone, created_at FROM users ORDER BY created_at DESC");
    }

    // Get users by role
    public function getByRole($role) {
        return $this->db->fetchAll(
            "SELECT id, username, first_name, last_name, email, phone FROM users WHERE role = ? ORDER BY first_name",
            [$role]
        );
    }

    // Create new user
    public function create($data) {
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        $this->db->execute(
            "INSERT INTO users (username, password, role, first_name, last_name, email, phone) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $data['username'],
                $hashedPassword,
                $data['role'],
                $data['first_name'],
                $data['last_name'],
                $data['email'] ?? null,
                $data['phone'] ?? null
            ]
        );

        return $this->db->lastInsertId();
    }

    // Update user
    public function update($id, $data) {
        $fields = [];
        $params = [];

        if (isset($data['first_name'])) {
            $fields[] = "first_name = ?";
            $params[] = $data['first_name'];
        }
        if (isset($data['last_name'])) {
            $fields[] = "last_name = ?";
            $params[] = $data['last_name'];
        }
        if (isset($data['email'])) {
            $fields[] = "email = ?";
            $params[] = $data['email'];
        }
        if (isset($data['phone'])) {
            $fields[] = "phone = ?";
            $params[] = $data['phone'];
        }
        if (isset($data['password']) && !empty($data['password'])) {
            $fields[] = "password = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";

        return $this->db->execute($sql, $params);
    }

    // Delete user
    public function delete($id) {
        return $this->db->execute("DELETE FROM users WHERE id = ?", [$id]);
    }

    // Check if username exists
    public function usernameExists($username, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM users WHERE username = ?";
        $params = [$username];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = $this->db->fetch($sql, $params);
        return $result['count'] > 0;
    }
}
?>
