<?php
/**
 * Checkin Model
 */

require_once __DIR__ . '/../config/Database.php';

class Checkin {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create new check-in
     */
    public function create($registration_id, $event_id, $user_id) {
        $stmt = $this->db->prepare(
            "INSERT INTO checkins (registration_id, event_id, checked_in_by) 
             VALUES (?, ?, ?)"
        );
        
        try {
            return $stmt->execute([$registration_id, $event_id, $user_id]);
        } catch (PDOException $e) {
            // Check for duplicate entry (already checked in)
            if ($e->getCode() == 23000) {
                return false;
            }
            throw $e;
        }
    }

    /**
     * Check if registration is already checked in
     */
    public function isCheckedIn($registration_id) {
        $stmt = $this->db->prepare(
            "SELECT id FROM checkins WHERE registration_id = ?"
        );
        $stmt->execute([$registration_id]);
        return $stmt->fetch() !== false;
    }

    /**
     * Get check-in details
     */
    public function getByRegistration($registration_id) {
        $stmt = $this->db->prepare(
            "SELECT c.*, u.full_name as checked_in_by_name
             FROM checkins c
             JOIN users u ON c.checked_in_by = u.id
             WHERE c.registration_id = ?"
        );
        $stmt->execute([$registration_id]);
        return $stmt->fetch();
    }

    /**
     * Get all check-ins for an event
     */
    public function getByEvent($event_id) {
        $stmt = $this->db->prepare(
            "SELECT c.*, r.attendee_name, r.attendee_email, r.attendee_phone,
                    u.full_name as checked_in_by_name
             FROM checkins c
             JOIN registrations r ON c.registration_id = r.id
             JOIN users u ON c.checked_in_by = u.id
             WHERE c.event_id = ?
             ORDER BY c.checkin_time DESC"
        );
        $stmt->execute([$event_id]);
        return $stmt->fetchAll();
    }

    /**
     * Get check-in count for event
     */
    public function getCountByEvent($event_id) {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as count FROM checkins WHERE event_id = ?"
        );
        $stmt->execute([$event_id]);
        $result = $stmt->fetch();
        return $result['count'];
    }

    /**
     * Delete check-in (for admin only)
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM checkins WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
