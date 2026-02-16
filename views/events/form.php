<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="bi bi-<?php echo isset($event) ? 'pencil' : 'plus-circle'; ?>"></i>
                    <?php echo isset($event) ? 'Editar Evento' : 'Crear Nuevo Evento'; ?>
                </h4>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo BASE_URL; ?>/index.php?action=<?php echo isset($event) ? 'event_edit&id=' . $event['id'] : 'event_create'; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nombre del Evento <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($event['name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="location" class="form-label">Ubicación</label>
                            <input type="text" class="form-control" id="location" name="location" 
                                   value="<?php echo htmlspecialchars($event['location'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($event['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="event_date" class="form-label">Fecha <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="event_date" name="event_date" 
                                   value="<?php echo $event['event_date'] ?? ''; ?>" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="start_time" class="form-label">Hora Inicio <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="start_time" name="start_time" 
                                   value="<?php echo $event['start_time'] ?? ''; ?>" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="end_time" class="form-label">Hora Fin</label>
                            <input type="time" class="form-control" id="end_time" name="end_time" 
                                   value="<?php echo $event['end_time'] ?? ''; ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="max_attendees" class="form-label">Capacidad Máxima</label>
                            <input type="number" class="form-control" id="max_attendees" name="max_attendees" 
                                   value="<?php echo $event['max_attendees'] ?? ''; ?>" min="1">
                            <div class="form-text">Deje en blanco para capacidad ilimitada</div>
                        </div>
                        
                        <?php if (isset($event)): ?>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estado</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                       <?php echo ($event['is_active'] ?? 1) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_active">
                                    Evento Activo
                                </label>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?php echo BASE_URL; ?>/index.php?action=events" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> <?php echo isset($event) ? 'Actualizar' : 'Crear'; ?> Evento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
