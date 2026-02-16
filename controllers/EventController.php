<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Event.php';

class EventController extends Controller {
    private $eventModel;
    
    public function __construct() {
        $this->eventModel = new Event();
    }
    
    public function index() {
        $this->requireLogin();
        
        $events = $this->eventModel->getActiveEvents();
        
        echo $this->view('shared/header', ['user' => $_SESSION]);
        echo $this->view('events/list', ['events' => $events]);
        echo $this->view('shared/footer');
    }
    
    public function create() {
        $this->requireRole(['admin', 'operador']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            echo $this->view('shared/header', ['user' => $_SESSION]);
            echo $this->view('events/form', ['csrf_token' => $this->generateCSRF()]);
            echo $this->view('shared/footer');
            return;
        }
        
        $this->validateCSRF();
        
        $data = [
            'name' => $this->sanitizeInput($_POST['name'] ?? ''),
            'description' => $this->sanitizeInput($_POST['description'] ?? ''),
            'location' => $this->sanitizeInput($_POST['location'] ?? ''),
            'event_date' => $this->sanitizeInput($_POST['event_date'] ?? ''),
            'start_time' => $this->sanitizeInput($_POST['start_time'] ?? ''),
            'end_time' => $this->sanitizeInput($_POST['end_time'] ?? ''),
            'max_attendees' => !empty($_POST['max_attendees']) ? (int)$_POST['max_attendees'] : null
        ];
        
        $result = $this->eventModel->create($data);
        
        if (isset($result['success'])) {
            $_SESSION['success'] = 'Evento creado exitosamente';
            $this->redirect(BASE_URL . '/index.php?action=events');
        } else {
            $_SESSION['error'] = 'Error al crear el evento';
            $this->redirect(BASE_URL . '/index.php?action=event_create');
        }
    }
    
    public function view() {
        $this->requireLogin();
        
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            $this->redirect(BASE_URL . '/index.php?action=events');
        }
        
        $event = $this->eventModel->getEventWithStats($id);
        
        if (!$event) {
            $_SESSION['error'] = 'Evento no encontrado';
            $this->redirect(BASE_URL . '/index.php?action=events');
        }
        
        echo $this->view('shared/header', ['user' => $_SESSION]);
        echo $this->view('events/view', ['event' => $event]);
        echo $this->view('shared/footer');
    }
    
    public function edit() {
        $this->requireRole(['admin', 'operador']);
        
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            $this->redirect(BASE_URL . '/index.php?action=events');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $event = $this->eventModel->findById($id);
            
            if (!$event) {
                $_SESSION['error'] = 'Evento no encontrado';
                $this->redirect(BASE_URL . '/index.php?action=events');
            }
            
            echo $this->view('shared/header', ['user' => $_SESSION]);
            echo $this->view('events/form', [
                'event' => $event,
                'csrf_token' => $this->generateCSRF()
            ]);
            echo $this->view('shared/footer');
            return;
        }
        
        $this->validateCSRF();
        
        $data = [
            'name' => $this->sanitizeInput($_POST['name'] ?? ''),
            'description' => $this->sanitizeInput($_POST['description'] ?? ''),
            'location' => $this->sanitizeInput($_POST['location'] ?? ''),
            'event_date' => $this->sanitizeInput($_POST['event_date'] ?? ''),
            'start_time' => $this->sanitizeInput($_POST['start_time'] ?? ''),
            'end_time' => $this->sanitizeInput($_POST['end_time'] ?? ''),
            'max_attendees' => !empty($_POST['max_attendees']) ? (int)$_POST['max_attendees'] : null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        $result = $this->eventModel->update($id, $data);
        
        if (isset($result['success'])) {
            $_SESSION['success'] = 'Evento actualizado exitosamente';
        } else {
            $_SESSION['error'] = 'Error al actualizar el evento';
        }
        
        $this->redirect(BASE_URL . '/index.php?action=event_view&id=' . $id);
    }
}
