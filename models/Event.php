<?php
require_once __DIR__ . '/Model.php';

class Event extends Model {
    protected $table = 'events';
    
    public function create($data) {
        $data = $this->sanitize($data);
        
        if (empty($data['name']) || empty($data['event_date']) || empty($data['start_time'])) {
            return ['error' => 'missing_required_fields'];
        }
        
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} 
             (name, description, location, event_date, start_time, end_time, max_attendees, created_by) 
             VALUES (:name, :description, :location, :event_date, :start_time, :end_time, :max_attendees, :created_by)"
        );
        
        $result = $stmt->execute([
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null,
            ':location' => $data['location'] ?? null,
            ':event_date' => $data['event_date'],
            ':start_time' => $data['start_time'],
            ':end_time' => $data['end_time'] ?? null,
            ':max_attendees' => $data['max_attendees'] ?? null,
            ':created_by' => $_SESSION['user_id']
        ]);
        
        if ($result) {
            $eventId = $this->db->lastInsertId();
            $this->logAudit('event_created', 'event', $eventId, "Event created: {$data['name']}");
            return ['success' => true, 'id' => $eventId];
        }
        
        return ['error' => 'creation_failed'];
    }
    
    public function update($id, $data) {
        $data = $this->sanitize($data);
        $fields = [];
        $params = [':id' => $id];
        
        $allowedFields = ['name', 'description', 'location', 'event_date', 'start_time', 'end_time', 'max_attendees', 'is_active'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return ['error' => 'no_fields_to_update'];
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute($params)) {
            $this->logAudit('event_updated', 'event', $id, "Event updated");
            return ['success' => true];
        }
        
        return ['error' => 'update_failed'];
    }
    
    public function getActiveEvents() {
        $stmt = $this->db->prepare(
            "SELECT e.*, u.full_name as creator_name,
                    (SELECT COUNT(*) FROM attendees WHERE event_id = e.id) as total_attendees,
                    (SELECT COUNT(*) FROM attendees WHERE event_id = e.id AND is_checked_in = 1) as checked_in_count
             FROM {$this->table} e
             LEFT JOIN users u ON e.created_by = u.id
             WHERE e.is_active = 1
             ORDER BY e.event_date DESC, e.start_time DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getEventWithStats($id) {
        $stmt = $this->db->prepare(
            "SELECT e.*, u.full_name as creator_name,
                    (SELECT COUNT(*) FROM attendees WHERE event_id = e.id) as total_attendees,
                    (SELECT COUNT(*) FROM attendees WHERE event_id = e.id AND is_checked_in = 1) as checked_in_count
             FROM {$this->table} e
             LEFT JOIN users u ON e.created_by = u.id
             WHERE e.id = :id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
}
