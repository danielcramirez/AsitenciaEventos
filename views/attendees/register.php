<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-person-plus"></i> Registro para: <?php echo htmlspecialchars($event['name']); ?></h4>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Complete el formulario para registrarse al evento y obtener su código QR de acceso.
                </div>
                
                <form method="POST" action="<?php echo BASE_URL; ?>/index.php?action=attendee_register&event_id=<?php echo $event['id']; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div class="mb-3">
                        <label for="cedula" class="form-label">Cédula <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="cedula" name="cedula" 
                               pattern="[0-9]{6,20}" required 
                               placeholder="Ingrese su número de cédula">
                        <div class="form-text">Solo números, entre 6 y 20 dígitos</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="full_name" name="full_name" required 
                               placeholder="Ingrese su nombre completo">
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               placeholder="correo@ejemplo.com">
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               placeholder="Número de contacto">
                    </div>
                    
                    <div class="alert alert-warning">
                        <strong>Información del Evento:</strong>
                        <ul class="mb-0">
                            <li>Fecha: <?php echo date('d/m/Y', strtotime($event['event_date'])); ?></li>
                            <li>Hora: <?php echo date('H:i', strtotime($event['start_time'])); ?></li>
                            <?php if ($event['location']): ?>
                            <li>Lugar: <?php echo htmlspecialchars($event['location']); ?></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle"></i> Registrarse
                        </button>
                        <a href="<?php echo BASE_URL; ?>/index.php?action=events" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Volver a Eventos
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
