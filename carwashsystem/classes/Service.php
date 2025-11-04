<?php
// Service management class
require_once __DIR__ . '/Database.php';

class Service {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Get all service types
    public function getAllServiceTypes() {
        return $this->db->fetchAll("SELECT * FROM service_types ORDER BY name");
    }

    // Get service type by ID
    public function getServiceTypeById($id) {
        return $this->db->fetch("SELECT * FROM service_types WHERE id = ?", [$id]);
    }

    // Create new service type
    public function createServiceType($data) {
        $this->db->execute(
            "INSERT INTO service_types (name, description, duration_minutes, price) VALUES (?, ?, ?, ?)",
            [
                $data['name'],
                $data['description'],
                $data['duration_minutes'],
                $data['price']
            ]
        );
        return $this->db->lastInsertId();
    }

    // Update service type
    public function updateServiceType($id, $data) {
        return $this->db->execute(
            "UPDATE service_types SET name = ?, description = ?, duration_minutes = ?, price = ? WHERE id = ?",
            [
                $data['name'],
                $data['description'],
                $data['duration_minutes'],
                $data['price'],
                $id
            ]
        );
    }

    // Delete service type
    public function deleteServiceType($id) {
        return $this->db->execute("DELETE FROM service_types WHERE id = ?", [$id]);
    }

    // Get all vehicle types
    public function getAllVehicleTypes() {
        return $this->db->fetchAll("SELECT * FROM vehicle_types ORDER BY name");
    }

    // Get vehicle type by ID
    public function getVehicleTypeById($id) {
        return $this->db->fetch("SELECT * FROM vehicle_types WHERE id = ?", [$id]);
    }

    // Get service by ID
    public function getById($id) {
        return $this->db->fetch("SELECT * FROM service_types WHERE id = ?", [$id]);
    }

    // Create new vehicle type
    public function createVehicleType($data) {
        $this->db->execute(
            "INSERT INTO vehicle_types (name, description) VALUES (?, ?)",
            [$data['name'], $data['description']]
        );
        return $this->db->lastInsertId();
    }

    // Update vehicle type
    public function updateVehicleType($id, $data) {
        return $this->db->execute(
            "UPDATE vehicle_types SET name = ?, description = ? WHERE id = ?",
            [$data['name'], $data['description'], $id]
        );
    }

    // Delete vehicle type
    public function deleteVehicleType($id) {
        return $this->db->execute("DELETE FROM vehicle_types WHERE id = ?", [$id]);
    }

    // Get service history for a customer
    public function getCustomerServiceHistory($customerId) {
        return $this->db->fetchAll(
            "SELECT sh.*, st.name as service_name, st.price, v.plate_number,
                    u.first_name as staff_first_name, u.last_name as staff_last_name
             FROM service_history sh
             JOIN service_types st ON sh.service_type_id = st.id
             JOIN vehicles v ON sh.vehicle_id = v.id
             JOIN users u ON sh.staff_id = u.id
             WHERE sh.customer_id = ?
             ORDER BY sh.end_time DESC",
            [$customerId]
        );
    }

    // Get service statistics
    public function getServiceStats() {
        return $this->db->fetchAll(
            "SELECT st.name, COUNT(sh.id) as total_services,
                    AVG(TIMESTAMPDIFF(MINUTE, sh.start_time, sh.end_time)) as avg_duration,
                    SUM(st.price) as total_revenue
             FROM service_types st
             LEFT JOIN service_history sh ON st.id = sh.service_type_id
             WHERE DATE(sh.end_time) = CURDATE()
             GROUP BY st.id, st.name
             ORDER BY total_services DESC"
        );
    }
}
?>
