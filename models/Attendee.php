<?php
require_once __DIR__ . '/Model.php';

class Attendee extends Model {
    protected $table = 'attendees';
    
    public function register($eventId, $data) {
        $data = $this->sanitize($data);
        
        // Validate required fields
        if (empty($data['cedula']) || empty($data['full_name'])) {
            return ['error' => 'missing_required_fields'];
        }
        
        // Validate cedula format
        if (!$this->validateCedula($data['cedula'])) {
            return ['error' => 'invalid_cedula'];
        }
        
        // Validate email if provided
        if (!empty($data['email']) && !$this->validateEmail($data['email'])) {
            return ['error' => 'invalid_email'];
        }
        
        // Check if already registered for this event
        $stmt = $this->db->prepare(
            "SELECT id FROM {$this->table} WHERE event_id = :event_id AND cedula = :cedula"
        );
        $stmt->execute([':event_id' => $eventId, ':cedula' => $data['cedula']]);
        
        if ($stmt->fetch()) {
            return ['error' => 'already_registered'];
        }
        
        // Generate unique QR token
        $qrToken = $this->generateQRToken();
        
        // Insert attendee
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} 
             (event_id, cedula, full_name, email, phone, qr_token) 
             VALUES (:event_id, :cedula, :full_name, :email, :phone, :qr_token)"
        );
        
        $result = $stmt->execute([
            ':event_id' => $eventId,
            ':cedula' => $data['cedula'],
            ':full_name' => $data['full_name'],
            ':email' => $data['email'] ?? null,
            ':phone' => $data['phone'] ?? null,
            ':qr_token' => $qrToken
        ]);
        
        if ($result) {
            $attendeeId = $this->db->lastInsertId();
            $this->logAudit('attendee_registered', 'attendee', $attendeeId, 
                "Attendee registered for event $eventId: {$data['cedula']}");
            return [
                'success' => true, 
                'id' => $attendeeId, 
                'qr_token' => $qrToken
            ];
        }
        
        return ['error' => 'registration_failed'];
    }
    
    private function generateQRToken() {
        do {
            $token = bin2hex(random_bytes(QR_TOKEN_LENGTH / 2));
            $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE qr_token = :token");
            $stmt->execute([':token' => $token]);
        } while ($stmt->fetch());
        
        return $token;
    }
    
    public function regenerateQRToken($attendeeId) {
        // Implement rate limiting check
        if (!$this->checkRateLimit('qr_regenerate', $attendeeId)) {
            return ['error' => 'rate_limit_exceeded'];
        }
        
        // Generate new token and increment version
        $newToken = $this->generateQRToken();
        
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} 
             SET qr_token = :qr_token, 
                 token_version = token_version + 1,
                 qr_generated_at = NOW()
             WHERE id = :id"
        );
        
        if ($stmt->execute([':qr_token' => $newToken, ':id' => $attendeeId])) {
            $this->logAudit('qr_regenerated', 'attendee', $attendeeId, 
                "QR token regenerated");
            return ['success' => true, 'qr_token' => $newToken];
        }
        
        return ['error' => 'regeneration_failed'];
    }
    
    public function findByCedula($cedula, $eventId = null) {
        // Implement rate limiting
        if (!$this->checkRateLimit('qr_query', $cedula)) {
            return ['error' => 'rate_limit_exceeded'];
        }
        
        $cedula = $this->sanitize($cedula);
        
        if ($eventId) {
            $stmt = $this->db->prepare(
                "SELECT a.*, e.name as event_name, e.event_date, e.start_time
                 FROM {$this->table} a
                 JOIN events e ON a.event_id = e.id
                 WHERE a.cedula = :cedula AND a.event_id = :event_id"
            );
            $stmt->execute([':cedula' => $cedula, ':event_id' => $eventId]);
        } else {
            $stmt = $this->db->prepare(
                "SELECT a.*, e.name as event_name, e.event_date, e.start_time
                 FROM {$this->table} a
                 JOIN events e ON a.event_id = e.id
                 WHERE a.cedula = :cedula
                 ORDER BY e.event_date DESC"
            );
            $stmt->execute([':cedula' => $cedula]);
        }
        
        $this->logAudit('qr_query', 'attendee', null, "Query by cedula: $cedula", null);
        return $stmt->fetchAll();
    }
    
    public function findByQRToken($qrToken) {
        $qrToken = $this->sanitize($qrToken);
        
        $stmt = $this->db->prepare(
            "SELECT a.*, e.name as event_name, e.event_date, e.start_time, e.location
             FROM {$this->table} a
             JOIN events e ON a.event_id = e.id
             WHERE a.qr_token = :qr_token AND e.is_active = 1
             LIMIT 1"
        );
        $stmt->execute([':qr_token' => $qrToken]);
        return $stmt->fetch();
    }
    
    public function getEventAttendees($eventId) {
        $stmt = $this->db->prepare(
            "SELECT a.*, 
                    c.checked_in_at, c.validation_method,
                    u.full_name as checked_by_name
             FROM {$this->table} a
             LEFT JOIN checkins c ON a.id = c.attendee_id
             LEFT JOIN users u ON c.checked_in_by = u.id
             WHERE a.event_id = :event_id
             ORDER BY a.registered_at DESC"
        );
        $stmt->execute([':event_id' => $eventId]);
        return $stmt->fetchAll();
    }
    
    private function checkRateLimit($actionType, $identifier) {
        $identifier = $this->sanitize($identifier);
        
        $stmt = $this->db->prepare(
            "SELECT attempt_count, window_start 
             FROM rate_limits 
             WHERE identifier = :identifier AND action_type = :action_type"
        );
        $stmt->execute([
            ':identifier' => $identifier,
            ':action_type' => $actionType
        ]);
        
        $limit = $stmt->fetch();
        
        if (!$limit) {
            // First attempt - create record
            $stmt = $this->db->prepare(
                "INSERT INTO rate_limits (identifier, action_type, attempt_count) 
                 VALUES (:identifier, :action_type, 1)"
            );
            $stmt->execute([
                ':identifier' => $identifier,
                ':action_type' => $actionType
            ]);
            return true;
        }
        
        // Check if window has expired
        $windowStart = strtotime($limit['window_start']);
        if (time() - $windowStart > RATE_LIMIT_WINDOW) {
            // Reset window
            $stmt = $this->db->prepare(
                "UPDATE rate_limits 
                 SET attempt_count = 1, window_start = NOW() 
                 WHERE identifier = :identifier AND action_type = :action_type"
            );
            $stmt->execute([
                ':identifier' => $identifier,
                ':action_type' => $actionType
            ]);
            return true;
        }
        
        // Check if limit exceeded
        if ($limit['attempt_count'] >= RATE_LIMIT_QR_QUERIES) {
            $this->logAudit('rate_limit_exceeded', 'rate_limit', null, 
                "Action: $actionType, Identifier: $identifier", null);
            return false;
        }
        
        // Increment counter
        $stmt = $this->db->prepare(
            "UPDATE rate_limits 
             SET attempt_count = attempt_count + 1 
             WHERE identifier = :identifier AND action_type = :action_type"
        );
        $stmt->execute([
            ':identifier' => $identifier,
            ':action_type' => $actionType
        ]);
        
        return true;
    }
}
