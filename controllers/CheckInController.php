<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/CheckIn.php';
require_once __DIR__ . '/../models/Event.php';

class CheckInController extends Controller {
    private $checkInModel;
    private $eventModel;
    
    public function __construct() {
        $this->checkInModel = new CheckIn();
        $this->eventModel = new Event();
    }
    
    public function doorScreen() {
        $this->requireRole(['admin', 'operador']);
        
        $eventId = (int)($_GET['event_id'] ?? 0);
        
        if (!$eventId) {
            $_SESSION['error'] = 'Evento no especificado';
            $this->redirect(BASE_URL . '/index.php?action=events');
        }
        
        $event = $this->eventModel->getEventWithStats($eventId);
        
        if (!$event) {
            $_SESSION['error'] = 'Evento no encontrado';
            $this->redirect(BASE_URL . '/index.php?action=events');
        }
        
        $highContrast = isset($_GET['high_contrast']) ? true : false;
        
        echo $this->view('shared/header', ['user' => $_SESSION]);
        echo $this->view('checkin/door', [
            'event' => $event,
            'high_contrast' => $highContrast,
            'csrf_token' => $this->generateCSRF()
        ]);
        echo $this->view('shared/footer');
    }
    
    public function validate() {
        $this->requireRole(['admin', 'operador']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'invalid_request'], 400);
        }
        
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            // Fallback to POST data
            $input = $_POST;
        }
        
        $qrToken = $this->sanitizeInput($input['qr_token'] ?? '');
        
        if (empty($qrToken)) {
            $this->json(['error' => 'missing_qr_token', 'message' => 'Código QR no proporcionado'], 400);
        }
        
        $result = $this->checkInModel->validateAndCheckIn($qrToken);
        
        if (isset($result['error'])) {
            $statusCode = 400;
            
            if ($result['error'] === 'already_checked_in') {
                $statusCode = 409;
                $message = 'Ya ingresado previamente';
                $result['message'] = $message;
                $result['attendee_name'] = $result['attendee']['full_name'] ?? '';
            } else if ($result['error'] === 'invalid_qr_token') {
                $message = 'Código QR inválido';
                $result['message'] = $message;
            } else {
                $message = 'Error en la validación';
                $result['message'] = $message;
            }
            
            $this->json($result, $statusCode);
        }
        
        $this->json([
            'success' => true,
            'message' => 'Check-in exitoso',
            'attendee_name' => $result['attendee']['full_name'],
            'cedula' => $result['attendee']['cedula'],
            'event_name' => $result['attendee']['event_name'],
            'checked_in_at' => $result['checked_in_at']
        ], 200);
    }
    
    public function recentCheckIns() {
        $this->requireRole(['admin', 'operador']);
        
        $eventId = (int)($_GET['event_id'] ?? 0);
        $limit = (int)($_GET['limit'] ?? 10);
        
        if (!$eventId) {
            $this->json(['error' => 'missing_event_id'], 400);
        }
        
        $checkIns = $this->checkInModel->getEventCheckIns($eventId, $limit);
        
        $this->json([
            'success' => true,
            'checkins' => $checkIns
        ]);
    }
    
    public function exportCSV() {
        $this->requireRole(['admin', 'operador']);
        
        $eventId = (int)($_GET['event_id'] ?? 0);
        
        if (!$eventId) {
            $_SESSION['error'] = 'Evento no especificado';
            $this->redirect(BASE_URL . '/index.php?action=events');
        }
        
        $filepath = $this->checkInModel->exportCheckInsCSV($eventId);
        
        if (!$filepath || !file_exists($filepath)) {
            $_SESSION['error'] = 'No hay datos para exportar';
            $this->redirect(BASE_URL . '/index.php?action=event_view&id=' . $eventId);
        }
        
        $filename = basename($filepath);
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        
        readfile($filepath);
        unlink($filepath);
        exit;
    }
    
    public function stats() {
        $this->requireRole(['admin', 'operador']);
        
        $eventId = (int)($_GET['event_id'] ?? 0);
        
        if (!$eventId) {
            $this->json(['error' => 'missing_event_id'], 400);
        }
        
        $stats = $this->checkInModel->getCheckInStats($eventId);
        
        $this->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
}
