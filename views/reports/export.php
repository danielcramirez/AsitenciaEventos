<?php
/**
 * Export Reports to CSV
 */

require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../models/Event.php';
require_once __DIR__ . '/../../models/Registration.php';
require_once __DIR__ . '/../../models/Checkin.php';

secure_session_start();
require_role('Administrador');

$type = $_GET['type'] ?? '';
$event_id = intval($_GET['event_id'] ?? 0);

$eventModel = new Event();
$registrationModel = new Registration();
$checkinModel = new Checkin();

// Function to output CSV
function output_csv($filename, $headers, $data) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Output headers
    fputcsv($output, $headers);
    
    // Output data
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit();
}

// Events report
if ($type === 'events') {
    $events = $eventModel->getAll();
    $headers = ['ID', 'Nombre', 'Descripción', 'Ubicación', 'Fecha', 'Capacidad Máxima', 'Registrados', 'Disponibles', 'Check-ins', 'Creado Por', 'Estado', 'Fecha Creación'];
    $data = [];
    
    foreach ($events as $event) {
        $stats = $eventModel->getStats($event['id']);
        $data[] = [
            $event['id'],
            $event['name'],
            $event['description'],
            $event['location'],
            $event['event_date'],
            $event['max_capacity'],
            $event['current_registrations'],
            $stats['available_spots'],
            $stats['total_checkins'],
            $event['creator_name'],
            $event['active'] ? 'Activo' : 'Inactivo',
            $event['created_at']
        ];
    }
    
    output_csv('eventos_' . date('Y-m-d_His') . '.csv', $headers, $data);
}

// Registrations report (by event)
elseif ($type === 'registrations' && $event_id) {
    $event = $eventModel->getById($event_id);
    if (!$event) {
        die('Evento no encontrado');
    }
    
    $registrations = $registrationModel->getByEvent($event_id);
    $headers = ['ID', 'Nombre', 'Email', 'Teléfono', 'Token QR', 'Fecha Registro', 'Check-in', 'Fecha Check-in', 'Registrado Por'];
    $data = [];
    
    foreach ($registrations as $reg) {
        $data[] = [
            $reg['id'],
            $reg['attendee_name'],
            $reg['attendee_email'],
            $reg['attendee_phone'] ?? '',
            $reg['qr_token'],
            $reg['registration_date'],
            $reg['checked_in'] ? 'Sí' : 'No',
            $reg['checkin_time'] ?? '',
            $reg['registered_by_name']
        ];
    }
    
    $filename = 'registros_' . preg_replace('/[^a-zA-Z0-9]/', '_', $event['name']) . '_' . date('Y-m-d_His') . '.csv';
    output_csv($filename, $headers, $data);
}

// Check-ins report (by event)
elseif ($type === 'checkins' && $event_id) {
    $event = $eventModel->getById($event_id);
    if (!$event) {
        die('Evento no encontrado');
    }
    
    $checkins = $checkinModel->getByEvent($event_id);
    $headers = ['ID Check-in', 'Nombre', 'Email', 'Teléfono', 'Fecha Check-in', 'Check-in Por'];
    $data = [];
    
    foreach ($checkins as $checkin) {
        $data[] = [
            $checkin['id'],
            $checkin['attendee_name'],
            $checkin['attendee_email'],
            $checkin['attendee_phone'] ?? '',
            $checkin['checkin_time'],
            $checkin['checked_in_by_name']
        ];
    }
    
    $filename = 'checkins_' . preg_replace('/[^a-zA-Z0-9]/', '_', $event['name']) . '_' . date('Y-m-d_His') . '.csv';
    output_csv($filename, $headers, $data);
}

// All registrations report
elseif ($type === 'all_registrations') {
    $events = $eventModel->getAll();
    $headers = ['ID Registro', 'Evento', 'Nombre', 'Email', 'Teléfono', 'Token QR', 'Fecha Registro', 'Check-in', 'Fecha Check-in'];
    $data = [];
    
    foreach ($events as $event) {
        $registrations = $registrationModel->getByEvent($event['id']);
        foreach ($registrations as $reg) {
            $data[] = [
                $reg['id'],
                $event['name'],
                $reg['attendee_name'],
                $reg['attendee_email'],
                $reg['attendee_phone'] ?? '',
                $reg['qr_token'],
                $reg['registration_date'],
                $reg['checked_in'] ? 'Sí' : 'No',
                $reg['checkin_time'] ?? ''
            ];
        }
    }
    
    output_csv('todos_registros_' . date('Y-m-d_His') . '.csv', $headers, $data);
}

// All check-ins report
elseif ($type === 'all_checkins') {
    $events = $eventModel->getAll();
    $headers = ['ID Check-in', 'Evento', 'Nombre', 'Email', 'Teléfono', 'Fecha Check-in', 'Check-in Por'];
    $data = [];
    
    foreach ($events as $event) {
        $checkins = $checkinModel->getByEvent($event['id']);
        foreach ($checkins as $checkin) {
            $data[] = [
                $checkin['id'],
                $event['name'],
                $checkin['attendee_name'],
                $checkin['attendee_email'],
                $checkin['attendee_phone'] ?? '',
                $checkin['checkin_time'],
                $checkin['checked_in_by_name']
            ];
        }
    }
    
    output_csv('todos_checkins_' . date('Y-m-d_His') . '.csv', $headers, $data);
}

else {
    die('Tipo de reporte inválido');
}
