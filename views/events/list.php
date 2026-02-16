<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-calendar3"></i> Eventos</h2>
            <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'operador'])): ?>
            <a href="<?php echo BASE_URL; ?>/index.php?action=event_create" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Crear Evento
            </a>
            <?php endif; ?>
        </div>
        
        <?php if (empty($events)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No hay eventos disponibles en este momento.
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($events as $event): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><?php echo htmlspecialchars($event['name']); ?></h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text"><?php echo htmlspecialchars($event['description'] ?? 'Sin descripción'); ?></p>
                            
                            <ul class="list-unstyled">
                                <li><i class="bi bi-calendar"></i> <?php echo date('d/m/Y', strtotime($event['event_date'])); ?></li>
                                <li><i class="bi bi-clock"></i> <?php echo date('H:i', strtotime($event['start_time'])); ?></li>
                                <?php if ($event['location']): ?>
                                <li><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($event['location']); ?></li>
                                <?php endif; ?>
                            </ul>
                            
                            <div class="row text-center mt-3">
                                <div class="col-6">
                                    <small class="text-muted">Registrados</small>
                                    <h4><?php echo $event['total_attendees']; ?></h4>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Check-ins</small>
                                    <h4 class="text-success"><?php echo $event['checked_in_count']; ?></h4>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="d-flex gap-2">
                                <a href="<?php echo BASE_URL; ?>/index.php?action=event_view&id=<?php echo $event['id']; ?>" 
                                   class="btn btn-sm btn-outline-primary flex-fill">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                                <a href="<?php echo BASE_URL; ?>/index.php?action=attendee_register&event_id=<?php echo $event['id']; ?>" 
                                   class="btn btn-sm btn-success flex-fill">
                                    <i class="bi bi-person-plus"></i> Registrarse
                                </a>
                                <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'operador'])): ?>
                                <a href="<?php echo BASE_URL; ?>/index.php?action=checkin_door&event_id=<?php echo $event['id']; ?>" 
                                   class="btn btn-sm btn-warning flex-fill">
                                    <i class="bi bi-door-open"></i> Puerta
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
