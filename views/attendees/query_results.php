<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h4 class="mb-0"><i class="bi bi-search"></i> Resultados de Búsqueda</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Resultados para cédula: <strong><?php echo htmlspecialchars($cedula); ?></strong>
                </div>
                
                <?php if (empty($attendees)): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> No se encontraron registros para esta cédula.
                    </div>
                    <div class="text-center">
                        <a href="<?php echo BASE_URL; ?>/index.php?action=events" class="btn btn-primary">
                            <i class="bi bi-calendar3"></i> Ver Eventos Disponibles
                        </a>
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($attendees as $attendee): ?>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header <?php echo $attendee['is_checked_in'] ? 'bg-success' : 'bg-primary'; ?> text-white">
                                    <h5 class="mb-0">
                                        <?php echo htmlspecialchars($attendee['event_name']); ?>
                                        <?php if ($attendee['is_checked_in']): ?>
                                            <i class="bi bi-check-circle float-end"></i>
                                        <?php endif; ?>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($attendee['full_name']); ?></p>
                                    <p><strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($attendee['event_date'])); ?></p>
                                    <p><strong>Hora:</strong> <?php echo date('H:i', strtotime($attendee['start_time'])); ?></p>
                                    
                                    <?php if ($attendee['is_checked_in']): ?>
                                        <div class="alert alert-success mb-2">
                                            <i class="bi bi-check-circle"></i> Ya realizó check-in para este evento
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-warning mb-2">
                                            <i class="bi bi-clock"></i> Pendiente de check-in
                                        </div>
                                    <?php endif; ?>
                                    
                                    <a href="<?php echo BASE_URL; ?>/index.php?action=qr_view&id=<?php echo $attendee['id']; ?>" 
                                       class="btn btn-primary w-100">
                                        <i class="bi bi-qr-code"></i> Ver Código QR
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <div class="text-center mt-4">
                    <a href="<?php echo BASE_URL; ?>/index.php?action=qr_query" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Nueva Búsqueda
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
