<?php
// Database wrapper class for PDO operations
require_once __DIR__ . '/../config/database.php';

class Database {
    private $pdo;

    public function __construct() {
        $this->pdo = getDB();
    }

    // Execute a query and return results
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Database query failed: " . $e->getMessage());
        }
    }

    // Fetch a single row
    public function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    // Fetch all rows
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    // Execute a statement (INSERT, UPDATE, DELETE)
    public function execute($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    // Get last inserted ID
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    // Begin transaction
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    // Commit transaction
    public function commit() {
        return $this->pdo->commit();
    }

    // Rollback transaction
    public function rollback() {
        return $this->pdo->rollBack();
    }
}
?>
