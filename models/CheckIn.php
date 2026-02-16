<?php
require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Attendee.php';

class CheckIn extends Model {
    protected $table = 'checkins';
    
    public function validateAndCheckIn($qrToken, $validationMethod = 'qr') {
        $qrToken = $this->sanitize($qrToken);
        
        // Find attendee by QR token
        $attendeeModel = new Attendee();
        $attendee = $attendeeModel->findByQRToken($qrToken);
        
        if (!$attendee) {
            $this->logAudit('checkin_failed', 'checkin', null, 
                "Invalid QR token: $qrToken", null);
            return ['error' => 'invalid_qr_token'];
        }
        
        // Check if already checked in
        if ($attendee['is_checked_in']) {
            $this->logAudit('checkin_duplicate', 'checkin', $attendee['id'], 
                "Already checked in: {$attendee['cedula']}", null);
            return [
                'error' => 'already_checked_in',
                'attendee' => $attendee
            ];
        }
        
        // Perform check-in
        $this->db->beginTransaction();
        
        try {
            // Insert check-in record
            $stmt = $this->db->prepare(
                "INSERT INTO {$this->table} 
                 (attendee_id, event_id, checked_in_by, validation_method, ip_address, user_agent) 
                 VALUES (:attendee_id, :event_id, :checked_in_by, :validation_method, :ip_address, :user_agent)"
            );
            
            $stmt->execute([
                ':attendee_id' => $attendee['id'],
                ':event_id' => $attendee['event_id'],
                ':checked_in_by' => $_SESSION['user_id'] ?? null,
                ':validation_method' => $validationMethod,
                ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
            
            // Update attendee status
            $stmt = $this->db->prepare(
                "UPDATE attendees SET is_checked_in = 1 WHERE id = :id"
            );
            $stmt->execute([':id' => $attendee['id']]);
            
            $this->db->commit();
            
            $this->logAudit('checkin_success', 'checkin', $attendee['id'], 
                "Check-in successful: {$attendee['cedula']} for event {$attendee['event_name']}", 
                $_SESSION['user_id'] ?? null);
            
            return [
                'success' => true,
                'attendee' => $attendee,
                'checked_in_at' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Check-in error: " . $e->getMessage());
            return ['error' => 'checkin_failed'];
        }
    }
    
    public function getEventCheckIns($eventId, $limit = null) {
        $sql = "SELECT c.*, a.cedula, a.full_name, a.email, 
                       u.full_name as checked_by_name
                FROM {$this->table} c
                JOIN attendees a ON c.attendee_id = a.id
                LEFT JOIN users u ON c.checked_in_by = u.id
                WHERE c.event_id = :event_id
                ORDER BY c.checked_in_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT :limit";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':event_id', $eventId, PDO::PARAM_INT);
        
        if ($limit) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getCheckInStats($eventId) {
        $stmt = $this->db->prepare(
            "SELECT 
                COUNT(*) as total_checkins,
                COUNT(DISTINCT DATE(checked_in_at)) as days_with_checkins,
                MIN(checked_in_at) as first_checkin,
                MAX(checked_in_at) as last_checkin,
                SUM(CASE WHEN validation_method = 'qr' THEN 1 ELSE 0 END) as qr_checkins,
                SUM(CASE WHEN validation_method = 'manual' THEN 1 ELSE 0 END) as manual_checkins,
                SUM(CASE WHEN validation_method = 'cedula' THEN 1 ELSE 0 END) as cedula_checkins
             FROM {$this->table}
             WHERE event_id = :event_id"
        );
        $stmt->execute([':event_id' => $eventId]);
        return $stmt->fetch();
    }
    
    public function exportCheckInsCSV($eventId) {
        $checkIns = $this->getEventCheckIns($eventId);
        
        if (empty($checkIns)) {
            return null;
        }
        
        $filename = 'checkins_event_' . $eventId . '_' . date('Y-m-d_His') . '.csv';
        $filepath = sys_get_temp_dir() . '/' . $filename;
        
        $file = fopen($filepath, 'w');
        
        // UTF-8 BOM for Excel compatibility
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Header
        fputcsv($file, ['Cédula', 'Nombre', 'Email', 'Fecha Check-in', 'Método', 'Validado por']);
        
        // Data
        foreach ($checkIns as $checkIn) {
            fputcsv($file, [
                $checkIn['cedula'],
                $checkIn['full_name'],
                $checkIn['email'] ?? '',
                $checkIn['checked_in_at'],
                $checkIn['validation_method'],
                $checkIn['checked_by_name'] ?? 'Sistema'
            ]);
        }
        
        fclose($file);
        
        return $filepath;
    }
}
