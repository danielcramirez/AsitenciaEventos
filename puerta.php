<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

require_role(['ADMIN','OPERATOR']);

$events = db()->query("SELECT id,nombre FROM events WHERE estado='PUBLISHED' ORDER BY fecha_inicio DESC")->fetchAll();
$event_id = (int)($_GET['event_id'] ?? ($events[0]['id'] ?? 0));

require __DIR__ . '/header.php';
?>
<h3 class="mb-3">Puerta (Check-in)</h3>

<div class="card shadow-sm mb-3">
  <div class="card-body">
    <form class="row g-2" method="get">
      <div class="col-md-8">
        <select class="form-select" name="event_id" required>
          <?php foreach ($events as $e): ?>
            <option value="<?= (int)$e['id'] ?>" <?= ((int)$e['id']===$event_id?'selected':'') ?>>
              <?= h($e['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <button class="btn btn-outline-dark w-100">Seleccionar evento</button>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="mb-3">
      <h5>Escáner de QR</h5>
      <div id="reader" style="width:100%; max-width:400px;"></div>
    </div>

    <div class="mb-3">
      <h5>O pega el token manualmente</h5>
      <div class="input-group">
        <input id="token" class="form-control" placeholder="Token del QR">
        <button class="btn btn-brand" id="btn">Validar</button>
      </div>
    </div>

    <div id="result"></div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.4/html5-qrcode.min.js"></script>
<script>
let scannerStarted = false;

function processQR(token) {
  token = token.trim();
  if (!token) return;
  
  const eventId = <?= (int)$event_id ?>;
  fetch('<?= BASE_URL ?>/api_checkin.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({ token, event_id: eventId })
  })
  .then(r => r.json())
  .then(data => {
    const el = document.getElementById('result');
    el.innerHTML = `
      <div class="alert ${data.ok ? (data.already ? 'alert-warning' : 'alert-success') : 'alert-danger'}">
        <h4 class="mb-1">${data.message}</h4>
        ${data.person ? `<div><strong>${data.person.nombres} ${data.person.apellidos}</strong></div>
        <div>Cédula: ${data.person.cedula}</div>` : ''}
        ${data.checkin_at ? `<div class="small">Hora: ${data.checkin_at}</div>` : ''}
      </div>`;
    document.getElementById('token').value = '';
  });
}

// Botón de validación manual
document.getElementById('btn').addEventListener('click', () => {
  processQR(document.getElementById('token').value);
});

// Lector de QR con cámara
const html5QrcodeScanner = new Html5QrcodeScanner(
  "reader",
  { fps: 10, qrbox: {width: 250, height: 250} },
  false
);

html5QrcodeScanner.render(
  (token) => {
    scannerStarted = true;
    // Detener scanner después de leer
    html5QrcodeScanner.pause(true);
    processQR(token);
    // Reanudar después de 2 segundos
    setTimeout(() => html5QrcodeScanner.pause(false), 2000);
  },
  (error) => {
    if (scannerStarted) console.log('QR error:', error);
  }
);
</script>

<?php require __DIR__ . '/footer.php'; ?>
