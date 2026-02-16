<?php
/**
 * QR Scanner Page
 */

require_once __DIR__ . '/../../config/helpers.php';

secure_session_start();
require_login();

$page_title = 'Escanear Código QR';

include __DIR__ . '/../layouts/header.php';
?>

<div class="card">
    <div class="card-header">
        <h1 class="card-title">📷 Escanear Código QR</h1>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <div>
            <h3 style="margin-bottom: 1rem;">Cámara del Escáner</h3>
            <div id="reader" style="border: 2px solid #e0e0e0; border-radius: 10px; overflow: hidden;"></div>
            <p style="margin-top: 1rem; color: #666; text-align: center;">
                Posiciona el código QR frente a la cámara
            </p>
        </div>

        <div>
            <h3 style="margin-bottom: 1rem;">Resultado del Escaneo</h3>
            <div id="result" style="background: #f7fafc; padding: 2rem; border-radius: 10px; min-height: 200px; text-align: center; color: #666;">
                Esperando escaneo...
            </div>

            <div style="margin-top: 2rem;">
                <h3 style="margin-bottom: 1rem;">O ingresa el token manualmente:</h3>
                <form id="manualForm" method="GET" action="/views/qr/validate.php">
                    <div class="form-group">
                        <input type="text" name="token" class="form-control" placeholder="Ingresa el token del QR" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Validar Token</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
function onScanSuccess(decodedText, decodedResult) {
    // Play success sound (optional)
    const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBTGH0fPTgjMGHm7A7+OZWQ0OVqvk7bNeHAQ+mtvy');
    audio.play().catch(e => console.log('Audio play failed:', e));
    
    // Show loading
    document.getElementById('result').innerHTML = '<div style="padding: 2rem;"><div style="font-size: 3rem;">⏳</div><div style="margin-top: 1rem; font-size: 1.2rem;">Validando...</div></div>';
    
    // Redirect to validation page
    window.location.href = '/views/qr/validate.php?token=' + encodeURIComponent(decodedText);
}

function onScanError(errorMessage) {
    // Silently handle scan errors (they're continuous while scanning)
}

const html5QrCode = new Html5Qrcode("reader");
const config = { 
    fps: 10, 
    qrbox: { width: 250, height: 250 },
    aspectRatio: 1.0
};

// Start camera
html5QrCode.start(
    { facingMode: "environment" }, // Use back camera on mobile
    config,
    onScanSuccess,
    onScanError
).catch(err => {
    // Try with front camera if back camera fails
    html5QrCode.start(
        { facingMode: "user" },
        config,
        onScanSuccess,
        onScanError
    ).catch(err => {
        document.getElementById('result').innerHTML = 
            '<div style="color: #f56565; padding: 2rem;">' +
            '<div style="font-size: 3rem;">❌</div>' +
            '<div style="margin-top: 1rem;"><strong>Error al acceder a la cámara</strong></div>' +
            '<div style="margin-top: 0.5rem; font-size: 0.9rem;">Por favor, permite el acceso a la cámara o usa la opción manual.</div>' +
            '</div>';
    });
});

// Stop camera when leaving page
window.addEventListener('beforeunload', () => {
    html5QrCode.stop().catch(err => console.log('Stop error:', err));
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
