<?php
/**
 * Registration Model
 */

require_once __DIR__ . '/../config/Database.php';

class Registration {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create new registration with QR token
     */
    public function create($data, $user_id) {
        // Generate unique QR token
        $qr_token = $this->generateUniqueToken();
        
        $stmt = $this->db->prepare(
            "INSERT INTO registrations (event_id, attendee_name, attendee_email, attendee_phone, qr_token, registered_by) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        
        $result = $stmt->execute([
            $data['event_id'],
            $data['attendee_name'],
            $data['attendee_email'],
            $data['attendee_phone'] ?? null,
            $qr_token,
            $user_id
        ]);

        if ($result) {
            return [
                'id' => $this->db->lastInsertId(),
                'qr_token' => $qr_token
            ];
        }

        return false;
    }

    /**
     * Generate unique QR token
     */
    private function generateUniqueToken() {
        do {
            $token = bin2hex(random_bytes(32));
            $stmt = $this->db->prepare("SELECT id FROM registrations WHERE qr_token = ?");
            $stmt->execute([$token]);
        } while ($stmt->fetch());
        
        return $token;
    }

    /**
     * Get registration by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare(
            "SELECT r.*, e.name as event_name, e.event_date, e.location,
                    CASE WHEN c.id IS NOT NULL THEN 1 ELSE 0 END as checked_in,
                    c.checkin_time
             FROM registrations r
             JOIN events e ON r.event_id = e.id
             LEFT JOIN checkins c ON r.id = c.registration_id
             WHERE r.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Get registration by QR token
     */
    public function getByQrToken($token) {
        $stmt = $this->db->prepare(
            "SELECT r.*, e.name as event_name, e.event_date, e.location, e.active as event_active,
                    CASE WHEN c.id IS NOT NULL THEN 1 ELSE 0 END as checked_in,
                    c.checkin_time
             FROM registrations r
             JOIN events e ON r.event_id = e.id
             LEFT JOIN checkins c ON r.id = c.registration_id
             WHERE r.qr_token = ?"
        );
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    /**
     * Get all registrations for an event
     */
    public function getByEvent($event_id) {
        $stmt = $this->db->prepare(
            "SELECT r.*,
                    CASE WHEN c.id IS NOT NULL THEN 1 ELSE 0 END as checked_in,
                    c.checkin_time,
                    u.full_name as registered_by_name
             FROM registrations r
             LEFT JOIN checkins c ON r.id = c.registration_id
             LEFT JOIN users u ON r.registered_by = u.id
             WHERE r.event_id = ?
             ORDER BY r.registration_date DESC"
        );
        $stmt->execute([$event_id]);
        return $stmt->fetchAll();
    }

    /**
     * Check if email is already registered for event
     */
    public function isEmailRegistered($event_id, $email) {
        $stmt = $this->db->prepare(
            "SELECT id FROM registrations WHERE event_id = ? AND attendee_email = ?"
        );
        $stmt->execute([$event_id, $email]);
        return $stmt->fetch() !== false;
    }

    /**
     * Delete registration
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM registrations WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get registration count for event
     */
    public function getCountByEvent($event_id) {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as count FROM registrations WHERE event_id = ?"
        );
        $stmt->execute([$event_id]);
        $result = $stmt->fetch();
        return $result['count'];
    }
}
