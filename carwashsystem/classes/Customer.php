<?php
// Customer management class
require_once __DIR__ . '/Database.php';

class Customer {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Create new customer
    public function create($data) {
        $this->db->execute(
            "INSERT INTO customers (first_name, last_name, phone, email) VALUES (?, ?, ?, ?)",
            [
                $data['first_name'],
                $data['last_name'],
                $data['phone'],
                $data['email'] ?? null
            ]
        );
        return $this->db->lastInsertId();
    }

    // Get customer by ID
    public function getById($id) {
        return $this->db->fetch("SELECT * FROM customers WHERE id = ?", [$id]);
    }

    // Get customer by phone
    public function getByPhone($phone) {
        return $this->db->fetch("SELECT * FROM customers WHERE phone = ?", [$phone]);
    }

    // Update customer
    public function update($id, $data) {
        return $this->db->execute(
            "UPDATE customers SET first_name = ?, last_name = ?, phone = ?, email = ? WHERE id = ?",
            [
                $data['first_name'],
                $data['last_name'],
                $data['phone'],
                $data['email'] ?? null,
                $id
            ]
        );
    }

    // Search customers
    public function search($query) {
        $sql = "SELECT * FROM customers WHERE first_name LIKE ? OR last_name LIKE ? OR phone LIKE ? ORDER BY first_name";
        $param = "%$query%";
        return $this->db->fetchAll($sql, [$param, $param, $param]);
    }

    // Get all customers
    public function getAll() {
        return $this->db->fetchAll("SELECT * FROM customers ORDER BY first_name");
    }

    // Get customer with vehicles
    public function getWithVehicles($customerId) {
        $customer = $this->getById($customerId);
        if (!$customer) return null;

        $vehicles = $this->db->fetchAll(
            "SELECT v.*, vt.name as vehicle_type_name FROM vehicles v JOIN vehicle_types vt ON v.vehicle_type_id = vt.id WHERE v.customer_id = ?",
            [$customerId]
        );

        $customer['vehicles'] = $vehicles;
        return $customer;
    }
}
?>
