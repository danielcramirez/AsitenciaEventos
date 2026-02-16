<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-in: <?php echo htmlspecialchars($event['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <style>
        body {
            background: <?php echo $high_contrast ? '#000' : '#f8f9fa'; ?>;
            color: <?php echo $high_contrast ? '#fff' : '#000'; ?>;
        }
        .high-contrast {
            background: #000;
            color: #fff;
        }
        .high-contrast .card {
            background: #1a1a1a;
            color: #fff;
            border: 2px solid #fff;
        }
        .high-contrast .btn-primary {
            background: #fff;
            color: #000;
            border: 2px solid #fff;
        }
        .high-contrast .text-muted {
            color: #ccc !important;
        }
        #video-container {
            position: relative;
            width: 100%;
            max-width: 640px;
            margin: 0 auto;
        }
        #qr-video {
            width: 100%;
            border-radius: 10px;
            border: 3px solid <?php echo $high_contrast ? '#fff' : '#007bff'; ?>;
        }
        .scan-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 250px;
            height: 250px;
            border: 3px solid <?php echo $high_contrast ? '#0f0' : '#ffc107'; ?>;
            border-radius: 10px;
            pointer-events: none;
        }
        .scan-overlay::before,
        .scan-overlay::after {
            content: '';
            position: absolute;
            width: 30px;
            height: 30px;
        }
        .scan-overlay::before {
            top: -3px;
            left: -3px;
            border-top: 6px solid <?php echo $high_contrast ? '#0f0' : '#ffc107'; ?>;
            border-left: 6px solid <?php echo $high_contrast ? '#0f0' : '#ffc107'; ?>;
        }
        .scan-overlay::after {
            bottom: -3px;
            right: -3px;
            border-bottom: 6px solid <?php echo $high_contrast ? '#0f0' : '#ffc107'; ?>;
            border-right: 6px solid <?php echo $high_contrast ? '#0f0' : '#ffc107'; ?>;
        }
        .status-card {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            min-width: 300px;
        }
        .success-animation {
            animation: successPulse 0.6s ease-in-out;
        }
        @keyframes successPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .stats-box {
            font-size: 2rem;
            font-weight: bold;
        }
        .recent-checkins {
            max-height: 400px;
            overflow-y: auto;
        }
        .checkin-item {
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>
</head>
<body class="<?php echo $high_contrast ? 'high-contrast' : ''; ?>">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2><i class="bi bi-door-open"></i> Check-in: <?php echo htmlspecialchars($event['name']); ?></h2>
                        <p class="text-muted mb-0">
                            <i class="bi bi-calendar"></i> <?php echo date('d/m/Y', strtotime($event['event_date'])); ?>
                            <i class="bi bi-clock ms-3"></i> <?php echo date('H:i', strtotime($event['start_time'])); ?>
                            <?php if ($event['location']): ?>
                            <i class="bi bi-geo-alt ms-3"></i> <?php echo htmlspecialchars($event['location']); ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div>
                        <a href="<?php echo BASE_URL; ?>/index.php?action=event_view&id=<?php echo $event['id']; ?>" 
                           class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                        <a href="?action=checkin_door&event_id=<?php echo $event['id']; ?>&high_contrast=<?php echo $high_contrast ? '0' : '1'; ?>" 
                           class="btn btn-outline-secondary">
                            <i class="bi bi-circle-half"></i> <?php echo $high_contrast ? 'Normal' : 'Alto Contraste'; ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- QR Scanner -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-camera-video"></i> Escáner QR</h5>
                    </div>
                    <div class="card-body text-center">
                        <div id="video-container">
                            <video id="qr-video" autoplay playsinline></video>
                            <div class="scan-overlay"></div>
                        </div>
                        <div class="mt-3">
                            <span id="scan-status" class="badge bg-primary">Listo para escanear</span>
                        </div>
                        <div class="mt-3">
                            <button id="start-scan" class="btn btn-primary btn-lg">
                                <i class="bi bi-play-fill"></i> Iniciar Escáner
                            </button>
                            <button id="stop-scan" class="btn btn-danger btn-lg d-none">
                                <i class="bi bi-stop-fill"></i> Detener Escáner
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Stats -->
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <small class="text-muted">Total Registrados</small>
                                <div class="stats-box"><?php echo $event['total_attendees']; ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <small class="text-muted">Check-ins Realizados</small>
                                <div class="stats-box text-success" id="checked-in-count"><?php echo $event['checked_in_count']; ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <small class="text-muted">Pendientes</small>
                                <div class="stats-box text-warning" id="pending-count">
                                    <?php echo $event['total_attendees'] - $event['checked_in_count']; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Check-ins -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-list-check"></i> Últimos Check-ins</h5>
                    </div>
                    <div class="card-body recent-checkins" id="recent-checkins">
                        <p class="text-muted text-center">Los últimos check-ins aparecerán aquí</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Success Audio -->
    <audio id="success-sound" preload="auto">
        <source src="<?php echo BASE_URL; ?>/assets/sounds/success.mp3" type="audio/mpeg">
        <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiToIFmS57OihUhELTKXh8bllHAU2jdXwyXkqBSl+zPDakj4JGGm98OihUhELTKXh8bllHAU2jdXwyXkqBSl+zPDakj4JGGm98OihUhELTKXh8bllHAU2jdXwyXkqBSl+zPDakj4JGGm98OihUhELTKXh8bllHAU2jdXwyXkqBSl+zPDakj4JGGm98OihUhELTKXh8bllHAU2jdXwyXkqBSl+zPDakj4JGGm98OihUhELTKXh8bllHAU2jdXwyXkqBSl+zPDakj4JGGm98OihUhELTKXh8bllHAU2jdXwyXkqBSl+zPDakj4JGGm98OihUhELTKXh8bllHAU2jdXwyXkqBSl+zPDakj4JGGm98OihUhELTKXh8bllHAU2jdXwyXkqBSl+zPDakj4JGGm98OihUhELTKXh8bllHAU2jdXwyXkqBSl+zPDakj4JGGm98OihUhELTKXh8bllHAU2jdXwyXkqBSl+zPDakj4JGGm98OihUhELTKXh8bllHAU2jdXwyXkqBSl+zPDakj4JGGm98OihUhELTKXh8bllHAU2jdXwyXkqBSl+zPDakj4JGGm98OihUhELTKXh8bllHAU2jdXwyXkqBSl+zPDakj4JGGm98OihUhELTKXh8bllHAU2jdXwyXkqBSl+zPDakj4JGGm98OihUhELTKXh8bllHAU2jdXwyXkqBSl+zPDakj4JGGm98OihUhELTKXh8bllHAU2jdXwyXkqBSl+zPDakj4JGGm98OihUhELTKXh8bllHAU2jdXwyXkqBSl+zPDakj4JGGm98OihUhELTKXh8bllHAU2jdXwyXkqBSl+zPDakj4JGGm98OihUhELTKXh8bllHAU2jdXwyXkqBSl+zPDakj4JGGm98OihUhELTKXh8bllHAU2jdXwyXkqBSl+zPDakj4JGGm98OihUhELTKXh8bllHAU2jdXwyXkqBSl+zPDakj4JGGm98A==" type="audio/wav">
    </audio>
    <audio id="error-sound" preload="auto">
        <source src="<?php echo BASE_URL; ?>/assets/sounds/error.mp3" type="audio/mpeg">
    </audio>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
        const EVENT_ID = <?php echo $event['id']; ?>;
        const HIGH_CONTRAST = <?php echo $high_contrast ? 'true' : 'false'; ?>;
        
        let videoStream = null;
        let scanning = false;
        let processingQR = false;
        
        const video = document.getElementById('qr-video');
        const startBtn = document.getElementById('start-scan');
        const stopBtn = document.getElementById('stop-scan');
        const scanStatus = document.getElementById('scan-status');
        const checkedInCount = document.getElementById('checked-in-count');
        const pendingCount = document.getElementById('pending-count');
        const recentCheckIns = document.getElementById('recent-checkins');
        
        // Start scanner on page load
        window.addEventListener('load', () => {
            startScanner();
        });
        
        startBtn.addEventListener('click', startScanner);
        stopBtn.addEventListener('click', stopScanner);
        
        async function startScanner() {
            try {
                scanStatus.textContent = 'Iniciando cámara...';
                scanStatus.className = 'badge bg-warning';
                
                const constraints = {
                    video: {
                        facingMode: 'environment',
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    }
                };
                
                videoStream = await navigator.mediaDevices.getUserMedia(constraints);
                video.srcObject = videoStream;
                video.play();
                
                scanning = true;
                startBtn.classList.add('d-none');
                stopBtn.classList.remove('d-none');
                
                scanStatus.textContent = 'Escaneando...';
                scanStatus.className = 'badge bg-success';
                
                requestAnimationFrame(scanFrame);
                
            } catch (error) {
                console.error('Error accessing camera:', error);
                scanStatus.textContent = 'Error: No se puede acceder a la cámara';
                scanStatus.className = 'badge bg-danger';
                showError('No se puede acceder a la cámara. Por favor, permita el acceso.');
            }
        }
        
        function stopScanner() {
            scanning = false;
            if (videoStream) {
                videoStream.getTracks().forEach(track => track.stop());
                videoStream = null;
            }
            
            startBtn.classList.remove('d-none');
            stopBtn.classList.add('d-none');
            
            scanStatus.textContent = 'Escáner detenido';
            scanStatus.className = 'badge bg-secondary';
        }
        
        function scanFrame() {
            if (!scanning) return;
            
            if (video.readyState === video.HAVE_ENOUGH_DATA && !processingQR) {
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                
                const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height);
                
                if (code) {
                    processingQR = true;
                    scanStatus.textContent = 'Validando...';
                    scanStatus.className = 'badge bg-info';
                    validateQRCode(code.data);
                }
            }
            
            requestAnimationFrame(scanFrame);
        }
        
        async function validateQRCode(qrToken) {
            try {
                const response = await fetch(BASE_URL + '/index.php?action=checkin_validate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ qr_token: qrToken })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showSuccess(data);
                    playSound('success');
                    updateStats();
                    loadRecentCheckIns();
                } else {
                    showError(data.message || 'Error en la validación');
                    playSound('error');
                }
                
            } catch (error) {
                console.error('Validation error:', error);
                showError('Error de conexión');
                playSound('error');
            } finally {
                setTimeout(() => {
                    processingQR = false;
                    scanStatus.textContent = 'Escaneando...';
                    scanStatus.className = 'badge bg-success';
                }, 2000);
            }
        }
        
        function showSuccess(data) {
            scanStatus.textContent = '✓ Check-in exitoso';
            scanStatus.className = 'badge bg-success success-animation';
            
            const toast = `
                <div class="alert alert-success alert-dismissible fade show success-animation" role="alert">
                    <h5 class="alert-heading">✓ Check-in Exitoso</h5>
                    <p class="mb-1"><strong>${data.attendee_name}</strong></p>
                    <small>Cédula: ${data.cedula}</small>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = toast;
            tempDiv.style.position = 'fixed';
            tempDiv.style.top = '20px';
            tempDiv.style.right = '20px';
            tempDiv.style.zIndex = '9999';
            tempDiv.style.minWidth = '300px';
            
            document.body.appendChild(tempDiv);
            
            setTimeout(() => {
                tempDiv.remove();
            }, 5000);
        }
        
        function showError(message) {
            scanStatus.textContent = '✗ ' + message;
            scanStatus.className = 'badge bg-danger';
            
            const toast = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h5 class="alert-heading">✗ Error</h5>
                    <p class="mb-0">${message}</p>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = toast;
            tempDiv.style.position = 'fixed';
            tempDiv.style.top = '20px';
            tempDiv.style.right = '20px';
            tempDiv.style.zIndex = '9999';
            tempDiv.style.minWidth = '300px';
            
            document.body.appendChild(tempDiv);
            
            setTimeout(() => {
                tempDiv.remove();
            }, 5000);
        }
        
        function playSound(type) {
            const sound = document.getElementById(type + '-sound');
            if (sound) {
                sound.currentTime = 0;
                sound.play().catch(e => console.log('Could not play sound:', e));
            }
        }
        
        async function updateStats() {
            try {
                const response = await fetch(BASE_URL + '/index.php?action=checkin_stats&event_id=' + EVENT_ID);
                const data = await response.json();
                
                if (data.success && data.stats) {
                    const total = <?php echo $event['total_attendees']; ?>;
                    const checkedIn = data.stats.total_checkins || 0;
                    
                    checkedInCount.textContent = checkedIn;
                    pendingCount.textContent = total - checkedIn;
                }
            } catch (error) {
                console.error('Error updating stats:', error);
            }
        }
        
        async function loadRecentCheckIns() {
            try {
                const response = await fetch(BASE_URL + '/index.php?action=checkin_recent&event_id=' + EVENT_ID + '&limit=10');
                const data = await response.json();
                
                if (data.success && data.checkins && data.checkins.length > 0) {
                    recentCheckIns.innerHTML = data.checkins.map(checkin => `
                        <div class="checkin-item border-bottom pb-2 mb-2">
                            <div class="d-flex justify-content-between">
                                <strong>${checkin.full_name}</strong>
                                <small class="text-muted">${formatTime(checkin.checked_in_at)}</small>
                            </div>
                            <small class="text-muted">Cédula: ${checkin.cedula}</small>
                        </div>
                    `).join('');
                }
            } catch (error) {
                console.error('Error loading recent check-ins:', error);
            }
        }
        
        function formatTime(datetime) {
            const date = new Date(datetime);
            return date.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' });
        }
        
        // Load recent check-ins on page load
        loadRecentCheckIns();
        
        // Auto-refresh stats every 30 seconds
        setInterval(updateStats, 30000);
        setInterval(loadRecentCheckIns, 30000);
    </script>
</body>
</html>
