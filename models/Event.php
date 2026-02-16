<?php
/**
 * Event Model
 */

require_once __DIR__ . '/../config/Database.php';

class Event {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create new event
     */
    public function create($data, $user_id) {
        $stmt = $this->db->prepare(
            "INSERT INTO events (name, description, location, event_date, max_capacity, created_by) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        
        return $stmt->execute([
            $data['name'],
            $data['description'],
            $data['location'],
            $data['event_date'],
            $data['max_capacity'],
            $user_id
        ]);
    }

    /**
     * Get event by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare(
            "SELECT e.*, u.full_name as creator_name 
             FROM events e
             JOIN users u ON e.created_by = u.id
             WHERE e.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Get all events
     */
    public function getAll($active_only = false) {
        $sql = "SELECT e.*, u.full_name as creator_name,
                (e.max_capacity - e.current_registrations) as available_spots
                FROM events e
                JOIN users u ON e.created_by = u.id";
        
        if ($active_only) {
            $sql .= " WHERE e.active = 1";
        }
        
        $sql .= " ORDER BY e.event_date DESC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Update event
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare(
            "UPDATE events 
             SET name = ?, description = ?, location = ?, event_date = ?, max_capacity = ?
             WHERE id = ?"
        );
        
        return $stmt->execute([
            $data['name'],
            $data['description'],
            $data['location'],
            $data['event_date'],
            $data['max_capacity'],
            $id
        ]);
    }

    /**
     * Delete event
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM events WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Toggle event active status
     */
    public function toggleActive($id) {
        $stmt = $this->db->prepare(
            "UPDATE events SET active = NOT active WHERE id = ?"
        );
        return $stmt->execute([$id]);
    }

    /**
     * Check if event has available capacity
     */
    public function hasCapacity($id) {
        $stmt = $this->db->prepare(
            "SELECT (max_capacity - current_registrations) as available 
             FROM events 
             WHERE id = ?"
        );
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result && $result['available'] > 0;
    }

    /**
     * Increment registration count
     */
    public function incrementRegistrations($id) {
        $stmt = $this->db->prepare(
            "UPDATE events 
             SET current_registrations = current_registrations + 1 
             WHERE id = ?"
        );
        return $stmt->execute([$id]);
    }

    /**
     * Get event statistics
     */
    public function getStats($id) {
        $stmt = $this->db->prepare(
            "SELECT 
                e.max_capacity,
                e.current_registrations,
                (e.max_capacity - e.current_registrations) as available_spots,
                COUNT(DISTINCT c.id) as total_checkins,
                (e.current_registrations - COUNT(DISTINCT c.id)) as pending_checkins
             FROM events e
             LEFT JOIN checkins c ON e.id = c.event_id
             WHERE e.id = ?
             GROUP BY e.id"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
