<?php
// Vehicle management class
require_once __DIR__ . '/Database.php';

class Vehicle {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Create new vehicle
    public function create($data) {
        $this->db->execute(
            "INSERT INTO vehicles (customer_id, plate_number, vehicle_type_id, brand, model, color) VALUES (?, ?, ?, ?, ?, ?)",
            [
                $data['customer_id'],
                $data['plate_number'],
                $data['vehicle_type_id'],
                $data['brand'] ?? null,
                $data['model'] ?? null,
                $data['color'] ?? null
            ]
        );
        return $this->db->lastInsertId();
    }

    // Get vehicle by ID
    public function getById($id) {
        return $this->db->fetch(
            "SELECT v.*, vt.name as vehicle_type_name FROM vehicles v JOIN vehicle_types vt ON v.vehicle_type_id = vt.id WHERE v.id = ?",
            [$id]
        );
    }

    // Get vehicles by customer ID
    public function getByCustomerId($customerId) {
        return $this->db->fetchAll(
            "SELECT v.*, vt.name as vehicle_type_name FROM vehicles v JOIN vehicle_types vt ON v.vehicle_type_id = vt.id WHERE v.customer_id = ? ORDER BY v.created_at DESC",
            [$customerId]
        );
    }

    // Get vehicle by plate number
    public function getByPlateNumber($plateNumber) {
        return $this->db->fetch(
            "SELECT v.*, vt.name as vehicle_type_name FROM vehicles v JOIN vehicle_types vt ON v.vehicle_type_id = vt.id WHERE v.plate_number = ?",
            [$plateNumber]
        );
    }

    // Update vehicle
    public function update($id, $data) {
        return $this->db->execute(
            "UPDATE vehicles SET plate_number = ?, vehicle_type_id = ?, brand = ?, model = ?, color = ? WHERE id = ?",
            [
                $data['plate_number'],
                $data['vehicle_type_id'],
                $data['brand'] ?? null,
                $data['model'] ?? null,
                $data['color'] ?? null,
                $id
            ]
        );
    }

    // Delete vehicle
    public function delete($id) {
        return $this->db->execute("DELETE FROM vehicles WHERE id = ?", [$id]);
    }

    // Search vehicles
    public function search($query) {
        $sql = "SELECT v.*, vt.name as vehicle_type_name, c.first_name, c.last_name
                FROM vehicles v
                JOIN vehicle_types vt ON v.vehicle_type_id = vt.id
                JOIN customers c ON v.customer_id = c.id
                WHERE v.plate_number LIKE ? OR v.brand LIKE ? OR v.model LIKE ?
                ORDER BY v.plate_number";
        $param = "%$query%";
        return $this->db->fetchAll($sql, [$param, $param, $param]);
    }
}
?>
