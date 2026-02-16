<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap.php';
require_once __DIR__ . '/../models/EventModel.php';

class HomeController {
  public static function index(): void {
    $events = EventModel::getPublished();
    render_view('layout/header', ['title' => 'Eventos publicados']);
    render_view('home/index', ['events' => $events]);
    render_view('layout/footer');
  }
}
