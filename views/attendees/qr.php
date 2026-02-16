<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-success text-white text-center">
                <h4 class="mb-0"><i class="bi bi-check-circle"></i> ¡Registro Exitoso!</h4>
            </div>
            <div class="card-body text-center">
                <div class="alert alert-success">
                    <strong>Su código QR ha sido generado exitosamente.</strong>
                    <p class="mb-0">Presente este código en la entrada del evento.</p>
                </div>
                
                <div class="my-4">
                    <div id="qr-code" class="d-inline-block p-3 bg-white border rounded"></div>
                </div>
                
                <div class="alert alert-info">
                    <strong>Información de su registro:</strong>
                    <ul class="list-unstyled mb-0 mt-2">
                        <li><strong>Evento:</strong> <?php echo htmlspecialchars($event['name']); ?></li>
                        <li><strong>Nombre:</strong> <?php echo htmlspecialchars($attendee['full_name']); ?></li>
                        <li><strong>Cédula:</strong> <?php echo htmlspecialchars($attendee['cedula']); ?></li>
                        <li><strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($event['event_date'])); ?></li>
                        <li><strong>Hora:</strong> <?php echo date('H:i', strtotime($event['start_time'])); ?></li>
                        <?php if ($event['location']): ?>
                        <li><strong>Lugar:</strong> <?php echo htmlspecialchars($event['location']); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="d-grid gap-2">
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="bi bi-printer"></i> Imprimir Código QR
                    </button>
                    
                    <form method="POST" action="<?php echo BASE_URL; ?>/index.php?action=qr_regenerate" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="attendee_id" value="<?php echo $attendee['id']; ?>">
                        <button type="submit" class="btn btn-warning w-100" onclick="return confirm('¿Está seguro de regenerar el código QR? El código anterior dejará de funcionar.');">
                            <i class="bi bi-arrow-repeat"></i> Regenerar Código QR
                        </button>
                    </form>
                    
                    <a href="<?php echo BASE_URL; ?>/index.php?action=events" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Volver a Eventos
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-body">
                <h6><i class="bi bi-info-circle"></i> Instrucciones:</h6>
                <ol>
                    <li>Guarde o imprima este código QR</li>
                    <li>Puede consultar su código QR en cualquier momento usando su cédula</li>
                    <li>Presente el código QR en la entrada del evento para realizar el check-in</li>
                    <li>El código QR es único y personal, no lo comparta</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
    // Generate QR Code
    new QRCode(document.getElementById("qr-code"), {
        text: "<?php echo $attendee['qr_token']; ?>",
        width: 300,
        height: 300,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });
</script>

<style>
    @media print {
        nav, footer, .btn, .card:last-child {
            display: none !important;
        }
        body {
            background: white;
        }
    }
</style>
