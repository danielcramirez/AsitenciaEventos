<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap.php';
require_once __DIR__ . '/../models/EventModel.php';
require_once __DIR__ . '/../models/UserModel.php';

class EventController {
  public static function show(): void {
    $id = (int)($_GET['id'] ?? 0);
    $event = EventModel::findPublishedById($id);
    if (!$event) {
      render_error('Evento no disponible', 404);
    }

    $person = null;
    if (current_user()) {
      $person = UserModel::findPersonByUserId((int)current_user()['id']);
    }

    render_view('layout/header', ['title' => 'Evento']);
    render_view('events/show', ['event' => $event, 'person' => $person]);
    render_view('layout/footer');
  }
}
