<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h4 class="mb-0"><i class="bi bi-search"></i> Consultar Código QR</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Ingrese su número de cédula para consultar sus códigos QR de eventos registrados.
                </div>
                
                <form method="POST" action="<?php echo BASE_URL; ?>/index.php?action=qr_query">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div class="mb-3">
                        <label for="cedula" class="form-label">Número de Cédula</label>
                        <input type="text" class="form-control form-control-lg" id="cedula" name="cedula" 
                               pattern="[0-9]{6,20}" required 
                               placeholder="Ingrese su número de cédula"
                               autofocus>
                        <div class="form-text">Solo números, entre 6 y 20 dígitos</div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                        <a href="<?php echo BASE_URL; ?>/index.php?action=events" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Ver Eventos
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-body">
                <h6><i class="bi bi-shield-check"></i> Seguridad:</h6>
                <p class="mb-0">
                    Esta consulta tiene límite de intentos por seguridad. 
                    Si realiza demasiadas consultas en poco tiempo, deberá esperar antes de intentar nuevamente.
                </p>
            </div>
        </div>
    </div>
</div>
