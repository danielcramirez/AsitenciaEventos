<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-speedometer2"></i> Panel de Control</h2>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-white-50">Eventos Activos</h6>
                                <h2 class="card-title mb-0">
                                    <i class="bi bi-calendar3"></i>
                                </h2>
                            </div>
                            <div>
                                <a href="<?php echo BASE_URL; ?>/index.php?action=events" class="btn btn-light btn-sm">
                                    Ver todos
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-white-50">Consultar QR</h6>
                                <h2 class="card-title mb-0">
                                    <i class="bi bi-qr-code"></i>
                                </h2>
                            </div>
                            <div>
                                <a href="<?php echo BASE_URL; ?>/index.php?action=qr_query" class="btn btn-light btn-sm">
                                    Consultar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if (in_array($user['role'], ['admin', 'operador'])): ?>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-white-50">Crear Evento</h6>
                                <h2 class="card-title mb-0">
                                    <i class="bi bi-plus-circle"></i>
                                </h2>
                            </div>
                            <div>
                                <a href="<?php echo BASE_URL; ?>/index.php?action=event_create" class="btn btn-light btn-sm">
                                    Nuevo
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Acceso Rápido</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="<?php echo BASE_URL; ?>/index.php?action=events" class="list-group-item list-group-item-action">
                        <i class="bi bi-calendar3"></i> Ver todos los eventos
                    </a>
                    <a href="<?php echo BASE_URL; ?>/index.php?action=qr_query" class="list-group-item list-group-item-action">
                        <i class="bi bi-search"></i> Consultar código QR por cédula
                    </a>
                    <?php if (in_array($user['role'], ['admin', 'operador'])): ?>
                    <a href="<?php echo BASE_URL; ?>/index.php?action=event_create" class="list-group-item list-group-item-action">
                        <i class="bi bi-plus-circle"></i> Crear nuevo evento
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="alert alert-info mt-4" role="alert">
            <h5 class="alert-heading"><i class="bi bi-lightbulb"></i> Bienvenido, <?php echo htmlspecialchars($user['full_name']); ?>!</h5>
            <p>Su rol actual es: <strong><?php echo ucfirst($user['role']); ?></strong></p>
            <hr>
            <p class="mb-0">Utilice el menú de navegación para acceder a las diferentes funciones del sistema.</p>
        </div>
    </div>
</div>
