<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Attendee.php';
require_once __DIR__ . '/../models/Event.php';

class AttendeeController extends Controller {
    private $attendeeModel;
    private $eventModel;
    
    public function __construct() {
        $this->attendeeModel = new Attendee();
        $this->eventModel = new Event();
    }
    
    public function register() {
        $eventId = (int)($_GET['event_id'] ?? 0);
        
        if (!$eventId) {
            $_SESSION['error'] = 'Evento no especificado';
            $this->redirect(BASE_URL . '/index.php?action=events');
        }
        
        $event = $this->eventModel->findById($eventId);
        
        if (!$event || !$event['is_active']) {
            $_SESSION['error'] = 'Evento no disponible';
            $this->redirect(BASE_URL . '/index.php?action=events');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            echo $this->view('shared/header', ['user' => $_SESSION ?? null]);
            echo $this->view('attendees/register', [
                'event' => $event,
                'csrf_token' => $this->generateCSRF()
            ]);
            echo $this->view('shared/footer');
            return;
        }
        
        $this->validateCSRF();
        
        $data = [
            'cedula' => $this->sanitizeInput($_POST['cedula'] ?? ''),
            'full_name' => $this->sanitizeInput($_POST['full_name'] ?? ''),
            'email' => $this->sanitizeInput($_POST['email'] ?? ''),
            'phone' => $this->sanitizeInput($_POST['phone'] ?? '')
        ];
        
        $result = $this->attendeeModel->register($eventId, $data);
        
        if (isset($result['success'])) {
            $_SESSION['success'] = 'Registro exitoso';
            $_SESSION['qr_token'] = $result['qr_token'];
            $_SESSION['attendee_id'] = $result['id'];
            $this->redirect(BASE_URL . '/index.php?action=qr_view&id=' . $result['id']);
        } else {
            $_SESSION['error'] = $this->getErrorMessage($result['error']);
            $this->redirect(BASE_URL . '/index.php?action=attendee_register&event_id=' . $eventId);
        }
    }
    
    public function list() {
        $this->requireRole(['admin', 'operador']);
        
        $eventId = (int)($_GET['event_id'] ?? 0);
        
        if (!$eventId) {
            $this->redirect(BASE_URL . '/index.php?action=events');
        }
        
        $attendees = $this->attendeeModel->getEventAttendees($eventId);
        $event = $this->eventModel->findById($eventId);
        
        echo $this->view('shared/header', ['user' => $_SESSION]);
        echo $this->view('attendees/list', [
            'attendees' => $attendees,
            'event' => $event
        ]);
        echo $this->view('shared/footer');
    }
    
    public function queryByCedula() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            echo $this->view('shared/header', ['user' => $_SESSION ?? null]);
            echo $this->view('attendees/query', ['csrf_token' => $this->generateCSRF()]);
            echo $this->view('shared/footer');
            return;
        }
        
        $this->validateCSRF();
        
        $cedula = $this->sanitizeInput($_POST['cedula'] ?? '');
        
        if (empty($cedula)) {
            $_SESSION['error'] = 'Por favor ingrese una cédula';
            $this->redirect(BASE_URL . '/index.php?action=qr_query');
        }
        
        $result = $this->attendeeModel->findByCedula($cedula);
        
        if (is_array($result) && isset($result['error'])) {
            if ($result['error'] === 'rate_limit_exceeded') {
                $_SESSION['error'] = 'Demasiadas consultas. Por favor espere un momento';
            }
            $this->redirect(BASE_URL . '/index.php?action=qr_query');
        }
        
        echo $this->view('shared/header', ['user' => $_SESSION ?? null]);
        echo $this->view('attendees/query_results', [
            'attendees' => $result,
            'cedula' => $cedula
        ]);
        echo $this->view('shared/footer');
    }
    
    public function showQR() {
        $id = (int)($_GET['id'] ?? 0);
        
        if (!$id) {
            $_SESSION['error'] = 'Asistente no encontrado';
            $this->redirect(BASE_URL . '/index.php');
        }
        
        $attendee = $this->attendeeModel->findById($id);
        
        if (!$attendee) {
            $_SESSION['error'] = 'Asistente no encontrado';
            $this->redirect(BASE_URL . '/index.php');
        }
        
        $event = $this->eventModel->findById($attendee['event_id']);
        
        echo $this->view('shared/header', ['user' => $_SESSION ?? null]);
        echo $this->view('attendees/qr', [
            'attendee' => $attendee,
            'event' => $event
        ]);
        echo $this->view('shared/footer');
    }
    
    public function regenerateQR() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/index.php');
        }
        
        $this->validateCSRF();
        
        $id = (int)($_POST['attendee_id'] ?? 0);
        
        $result = $this->attendeeModel->regenerateQRToken($id);
        
        if (isset($result['success'])) {
            $_SESSION['success'] = 'Código QR regenerado exitosamente';
        } else {
            $_SESSION['error'] = $this->getErrorMessage($result['error']);
        }
        
        $this->redirect(BASE_URL . '/index.php?action=qr_view&id=' . $id);
    }
    
    private function getErrorMessage($errorCode) {
        $messages = [
            'missing_required_fields' => 'Por favor complete todos los campos requeridos',
            'invalid_cedula' => 'Cédula inválida',
            'invalid_email' => 'Email inválido',
            'already_registered' => 'Ya está registrado para este evento',
            'rate_limit_exceeded' => 'Demasiados intentos. Por favor espere',
            'regeneration_failed' => 'Error al regenerar código QR'
        ];
        
        return $messages[$errorCode] ?? 'Error desconocido';
    }
}
