<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="bi bi-calendar3"></i> <?php echo htmlspecialchars($event['name']); ?></h2>
                <p class="text-muted mb-0">
                    <?php if ($event['is_active']): ?>
                        <span class="badge bg-success">Activo</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Inactivo</span>
                    <?php endif; ?>
                </p>
            </div>
            <div>
                <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'operador'])): ?>
                    <a href="<?php echo BASE_URL; ?>/index.php?action=event_edit&id=<?php echo $event['id']; ?>" 
                       class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Editar
                    </a>
                    <a href="<?php echo BASE_URL; ?>/index.php?action=checkin_door&event_id=<?php echo $event['id']; ?>" 
                       class="btn btn-primary">
                        <i class="bi bi-door-open"></i> Pantalla Puerta
                    </a>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>/index.php?action=events" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
        </div>
        
        <div class="row g-4 mb-4">
            <div class="col-md-8">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información del Evento</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($event['description']): ?>
                            <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="bi bi-calendar text-primary"></i>
                                        <strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($event['event_date'])); ?>
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-clock text-primary"></i>
                                        <strong>Hora Inicio:</strong> <?php echo date('H:i', strtotime($event['start_time'])); ?>
                                    </li>
                                    <?php if ($event['end_time']): ?>
                                    <li class="mb-2">
                                        <i class="bi bi-clock-fill text-primary"></i>
                                        <strong>Hora Fin:</strong> <?php echo date('H:i', strtotime($event['end_time'])); ?>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <?php if ($event['location']): ?>
                                    <li class="mb-2">
                                        <i class="bi bi-geo-alt text-primary"></i>
                                        <strong>Lugar:</strong> <?php echo htmlspecialchars($event['location']); ?>
                                    </li>
                                    <?php endif; ?>
                                    <?php if ($event['max_attendees']): ?>
                                    <li class="mb-2">
                                        <i class="bi bi-people text-primary"></i>
                                        <strong>Capacidad:</strong> <?php echo $event['max_attendees']; ?> personas
                                    </li>
                                    <?php endif; ?>
                                    <li class="mb-2">
                                        <i class="bi bi-person text-primary"></i>
                                        <strong>Creado por:</strong> <?php echo htmlspecialchars($event['creator_name'] ?? 'N/A'); ?>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card text-center mb-3">
                    <div class="card-body">
                        <small class="text-muted">Total Registrados</small>
                        <h2 class="mb-0"><?php echo $event['total_attendees']; ?></h2>
                    </div>
                </div>
                
                <div class="card text-center mb-3">
                    <div class="card-body">
                        <small class="text-muted">Check-ins Realizados</small>
                        <h2 class="mb-0 text-success"><?php echo $event['checked_in_count']; ?></h2>
                    </div>
                </div>
                
                <div class="card text-center">
                    <div class="card-body">
                        <small class="text-muted">Pendientes</small>
                        <h2 class="mb-0 text-warning"><?php echo $event['total_attendees'] - $event['checked_in_count']; ?></h2>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-list"></i> Acciones Rápidas</h5>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="<?php echo BASE_URL; ?>/index.php?action=attendee_register&event_id=<?php echo $event['id']; ?>" 
                           class="btn btn-success w-100">
                            <i class="bi bi-person-plus"></i><br>
                            Registrarse
                        </a>
                    </div>
                    
                    <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'operador'])): ?>
                    <div class="col-md-3">
                        <a href="<?php echo BASE_URL; ?>/index.php?action=attendee_list&event_id=<?php echo $event['id']; ?>" 
                           class="btn btn-info w-100">
                            <i class="bi bi-people"></i><br>
                            Ver Asistentes
                        </a>
                    </div>
                    
                    <div class="col-md-3">
                        <a href="<?php echo BASE_URL; ?>/index.php?action=checkin_export&event_id=<?php echo $event['id']; ?>" 
                           class="btn btn-primary w-100">
                            <i class="bi bi-download"></i><br>
                            Exportar CSV
                        </a>
                    </div>
                    
                    <div class="col-md-3">
                        <a href="<?php echo BASE_URL; ?>/index.php?action=checkin_door&event_id=<?php echo $event['id']; ?>" 
                           class="btn btn-warning w-100">
                            <i class="bi bi-door-open"></i><br>
                            Check-in
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
