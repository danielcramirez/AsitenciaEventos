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
    <div class="d-flex align-items-center justify-content-between mb-2">
      <h5 class="mb-0">Escáner de QR</h5>
      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="hcToggle">
        <label class="form-check-label" for="hcToggle">Modo alto contraste</label>
      </div>
    </div>
    <div id="reader" style="width:100%; max-width:420px;"></div>
    <div id="status" class="small text-muted mt-2">Listo para escanear.</div>

    <div class="mt-4">
      <h6>Ingreso manual (auto-validación)</h6>
      <input id="token" class="form-control" placeholder="Token del QR">
    </div>

    <div id="result" class="mt-3"></div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.4/html5-qrcode.min.js"></script>
<script>
const eventId = <?= (int)$event_id ?>;
const tokenMinLen = <?= (int)TOKEN_MIN_LEN ?>;

const resultEl = document.getElementById('result');
const statusEl = document.getElementById('status');
const tokenInput = document.getElementById('token');
const hcToggle = document.getElementById('hcToggle');

let lastToken = '';
let busy = false;
let cooldownUntil = 0;

function setStatus(text, type = 'muted') {
  statusEl.className = `small text-${type}`;
  statusEl.textContent = text;
}

function beep(freq, duration = 0.12) {
  try {
    const ctx = new (window.AudioContext || window.webkitAudioContext)();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.type = 'sine';
    osc.frequency.value = freq;
    osc.connect(gain);
    gain.connect(ctx.destination);
    gain.gain.value = 0.08;
    osc.start();
    setTimeout(() => { osc.stop(); ctx.close(); }, duration * 1000);
  } catch (e) {}
}

function renderResult(data) {
  resultEl.innerHTML = `
    <div class="alert ${data.ok ? (data.already ? 'alert-warning' : 'alert-success') : 'alert-danger'}">
      <h4 class="mb-1">${data.message}</h4>
      ${data.person ? `<div><strong>${data.person.nombres} ${data.person.apellidos}</strong></div>
      <div>Cédula: ${data.person.cedula}</div>` : ''}
      ${data.checkin_at ? `<div class="small">Hora: ${data.checkin_at}</div>` : ''}
    </div>`;
}

async function processQR(token) {
  token = token.trim();
  if (!token || token.length < tokenMinLen) return;
  if (token === lastToken || busy) return;
  if (Date.now() < cooldownUntil) return;

  lastToken = token;
  busy = true;
  setStatus('Validando...', 'info');

  try {
    const r = await fetch('<?= BASE_URL ?>/api_checkin.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ token, event_id: eventId })
    });
    const data = await r.json();
    renderResult(data);
    setStatus(data.ok ? 'Validación completa.' : 'Error en validación.', data.ok ? 'success' : 'danger');
    beep(data.ok ? 880 : 220);
  } catch (e) {
    setStatus('Error de red. Reintenta.', 'danger');
    beep(220);
  } finally {
    busy = false;
    tokenInput.value = '';
    cooldownUntil = Date.now() + 1200;
    setTimeout(() => { lastToken = ''; }, 800);
  }
}

let inputTimer = null;
tokenInput.addEventListener('input', () => {
  clearTimeout(inputTimer);
  inputTimer = setTimeout(() => processQR(tokenInput.value), 300);
});

tokenInput.addEventListener('keydown', (e) => {
  if (e.key === 'Enter') {
    e.preventDefault();
    processQR(tokenInput.value);
  }
});

hcToggle.addEventListener('change', () => {
  document.body.classList.toggle('hc-mode', hcToggle.checked);
});

const html5 = new Html5Qrcode('reader');
const config = { fps: 12, qrbox: { width: 280, height: 280 } };

html5.start({ facingMode: 'environment' }, config,
  (decodedText) => {
    processQR(decodedText);
  },
  (errorMessage) => {
    if (!busy) setStatus('Escaneando...', 'muted');
  }
).then(() => {
  setStatus('Escáner iniciado.', 'success');
}).catch((err) => {
  setStatus('No se pudo acceder a la cámara.', 'danger');
});
</script>
