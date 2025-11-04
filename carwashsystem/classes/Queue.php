<?php
// Queue management class
require_once __DIR__ . '/Database.php';

class Queue {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Add customer to queue
    public function addToQueue($customerId, $vehicleId, $serviceTypeId) {
        $this->db->beginTransaction();

        try {
            // Insert into queue
            $this->db->execute(
                "INSERT INTO queue (customer_id, vehicle_id, service_type_id, status, position) VALUES (?, ?, ?, 'waiting', ?)",
                [$customerId, $vehicleId, $serviceTypeId, $this->getNextPosition()]
            );

            $queueId = $this->db->lastInsertId();

            // Update estimated wait time
            $this->updateEstimatedWaitTimes();

            $this->db->commit();
            return $queueId;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    // Get next position in queue
    private function getNextPosition() {
        $result = $this->db->fetch("SELECT MAX(position) as max_pos FROM queue WHERE status IN ('waiting', 'in_progress')");
        return ($result['max_pos'] ?? 0) + 1;
    }

    // Update estimated wait times for all waiting customers
    public function updateEstimatedWaitTimes() {
        $waiting = $this->db->fetchAll(
            "SELECT q.id, st.duration_minutes FROM queue q JOIN service_types st ON q.service_type_id = st.id WHERE q.status = 'waiting' ORDER BY q.position"
        );

        $totalWait = 0;
        foreach ($waiting as $item) {
            $this->db->execute(
                "UPDATE queue SET estimated_wait_time = ? WHERE id = ?",
                [$totalWait, $item['id']]
            );
            $totalWait += $item['duration_minutes'];
        }
    }

    // Get current queue
    public function getCurrentQueue() {
        return $this->db->fetchAll(
            "SELECT q.id, q.position, q.status, q.estimated_wait_time, q.created_at,
                    c.first_name, c.last_name, c.phone,
                    v.plate_number, vt.name as vehicle_type,
                    st.name as service_name, st.duration_minutes, st.price,
                    u.first_name as staff_first_name, u.last_name as staff_last_name
             FROM queue q
             JOIN customers c ON q.customer_id = c.id
             JOIN vehicles v ON q.vehicle_id = v.id
             JOIN vehicle_types vt ON v.vehicle_type_id = vt.id
             JOIN service_types st ON q.service_type_id = st.id
             LEFT JOIN users u ON q.staff_id = u.id
             WHERE q.status IN ('waiting', 'in_progress')
             ORDER BY q.position"
        );
    }

    // Start service for a queue item
    public function startService($queueId, $staffId) {
        $this->db->execute(
            "UPDATE queue SET status = 'in_progress', started_at = NOW(), staff_id = ? WHERE id = ? AND status = 'waiting'",
            [$staffId, $queueId]
        );

        $this->updateEstimatedWaitTimes();
        return $this->db->lastInsertId() > 0;
    }

    // Complete service for a queue item
    public function completeService($queueId, $paymentStatus = 'pending') {
        $this->db->beginTransaction();

        try {
            // Get queue item details
            $queueItem = $this->db->fetch(
                "SELECT customer_id, vehicle_id, service_type_id, staff_id, started_at FROM queue WHERE id = ?",
                [$queueId]
            );

            if (!$queueItem) {
                throw new Exception("Queue item not found");
            }

            // Update queue status
            $this->db->execute(
                "UPDATE queue SET status = 'completed', completed_at = NOW(), payment_status = ? WHERE id = ?",
                [$paymentStatus, $queueId]
            );

            // Add to service history
            $this->db->execute(
                "INSERT INTO service_history (queue_id, customer_id, vehicle_id, service_type_id, staff_id, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?, NOW())",
                [
                    $queueId,
                    $queueItem['customer_id'],
                    $queueItem['vehicle_id'],
                    $queueItem['service_type_id'],
                    $queueItem['staff_id'],
                    $queueItem['started_at']
                ]
            );

            // Update positions for remaining items
            $this->db->execute(
                "UPDATE queue SET position = position - 1 WHERE status = 'waiting' AND position > (SELECT position FROM (SELECT position FROM queue WHERE id = ?) as temp)",
                [$queueId]
            );

            $this->updateEstimatedWaitTimes();
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    // Cancel queue item
    public function cancelService($queueId) {
        $this->db->execute("UPDATE queue SET status = 'cancelled' WHERE id = ?", [$queueId]);

        // Update positions
        $this->db->execute(
            "UPDATE queue SET position = position - 1 WHERE status = 'waiting' AND position > (SELECT position FROM (SELECT position FROM queue WHERE id = ?) as temp)",
            [$queueId]
        );

        $this->updateEstimatedWaitTimes();
        return true;
    }

    // Get queue statistics
    public function getStats() {
        $stats = $this->db->fetch(
            "SELECT
                COUNT(CASE WHEN status = 'waiting' THEN 1 END) as waiting,
                COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as in_progress,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_today,
                AVG(estimated_wait_time) as avg_wait_time
             FROM queue
             WHERE DATE(created_at) = CURDATE()"
        );

        return $stats;
    }

    // Get all queue items (for admin overview)
    public function getAllQueueItems() {
        return $this->db->fetchAll(
            "SELECT q.*, c.first_name, c.last_name, c.phone, v.plate_number, st.name as service_name
             FROM queue q
             JOIN customers c ON q.customer_id = c.id
             JOIN vehicles v ON q.vehicle_id = v.id
             JOIN service_types st ON q.service_type_id = st.id
             ORDER BY q.created_at DESC"
        );
    }

    // Get next waiting customer
    public function getNextWaitingCustomer() {
        return $this->db->fetch(
            "SELECT q.*, c.first_name, c.last_name, c.phone, v.plate_number, st.name as service_name
             FROM queue q
             JOIN customers c ON q.customer_id = c.id
             JOIN vehicles v ON q.vehicle_id = v.id
             JOIN service_types st ON q.service_type_id = st.id
             WHERE q.status = 'waiting'
             ORDER BY q.position ASC
             LIMIT 1"
        );
    }

    // Get staff current services
    public function getStaffCurrentServices($staffId) {
        return $this->db->fetchAll(
            "SELECT q.*, c.first_name, c.last_name, c.phone, v.plate_number, st.name as service_name, st.price
             FROM queue q
             JOIN customers c ON q.customer_id = c.id
             JOIN vehicles v ON q.vehicle_id = v.id
             JOIN service_types st ON q.service_type_id = st.id
             WHERE q.staff_id = ? AND q.status = 'in_progress'
             ORDER BY q.started_at ASC",
            [$staffId]
        );
    }

    // Update queue status
    public function updateStatus($queueId, $status, $staffId = null) {
        if ($status === 'in_progress' && $staffId) {
            $this->db->execute(
                "UPDATE queue SET status = ?, started_at = NOW(), staff_id = ? WHERE id = ?",
                [$status, $staffId, $queueId]
            );
        } else {
            $this->db->execute(
                "UPDATE queue SET status = ? WHERE id = ?",
                [$status, $queueId]
            );
        }
        $this->updateEstimatedWaitTimes();
        return true;
    }
}
?>
