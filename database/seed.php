<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';

$pdo = db();

echo "🌱 Iniciando seeder de base de datos...\n\n";

try {
  // Verificar si ya existe admin
  $st = $pdo->prepare("SELECT id FROM users WHERE email = ?");
  $st->execute(['admin@local']);
  
  if ($st->fetch()) {
    echo "⚠️  Usuario admin@local ya existe. Saltando creación.\n";
  } else {
    $hash = password_hash('Admin123*', PASSWORD_BCRYPT, ['cost' => 10]);
    $ins = $pdo->prepare("INSERT INTO users(email, password_hash, role, active) VALUES(?, ?, ?, 1)");
    $ins->execute(['admin@local', $hash, 'ADMIN']);
    echo "✅ Usuario ADMIN creado:\n";
    echo "   Email: admin@local\n";
    echo "   Contraseña: Admin123*\n";
    echo "   Role: ADMIN\n\n";
  }

  // Crear usuario operador de ejemplo
  $st = $pdo->prepare("SELECT id FROM users WHERE email = ?");
  $st->execute(['operador@local']);
  
  if ($st->fetch()) {
    echo "⚠️  Usuario operador@local ya existe. Saltando creación.\n";
  } else {
    $hash = password_hash('Operador123*', PASSWORD_BCRYPT, ['cost' => 10]);
    $ins = $pdo->prepare("INSERT INTO users(email, password_hash, role, active) VALUES(?, ?, ?, 1)");
    $ins->execute(['operador@local', $hash, 'OPERATOR']);
    echo "✅ Usuario OPERATOR creado:\n";
    echo "   Email: operador@local\n";
    echo "   Contraseña: Operador123*\n";
    echo "   Role: OPERATOR\n\n";
  }

  // Crear evento de ejemplo
  $st = $pdo->prepare("SELECT id FROM events WHERE nombre = ?");
  $st->execute(['Conferencia de Tecnología 2026']);
  
  if ($st->fetch()) {
    echo "⚠️  Evento de ejemplo ya existe. Saltando creación.\n";
  } else {
    $ins = $pdo->prepare(
      "INSERT INTO events(nombre, lugar, fecha_inicio, fecha_fin, cupo, estado) VALUES(?, ?, ?, ?, ?, ?)"
    );
    $ins->execute([
      'Conferencia de Tecnología 2026',
      'Centro de Convenciones',
      date('Y-m-d H:i:s', strtotime('+1 week')),
      date('Y-m-d H:i:s', strtotime('+1 week +8 hours')),
      500,
      'PUBLISHED'
    ]);
    echo "✅ Evento de ejemplo creado:\n";
    echo "   Nombre: Conferencia de Tecnología 2026\n";
    echo "   Lugar: Centro de Convenciones\n";
    echo "   Cupo: 500\n";
    echo "   Estado: PUBLISHED\n\n";
  }

  // Crear persona y registro de ejemplo
  $st = $pdo->prepare("SELECT id FROM persons WHERE cedula = ?");
  $st->execute(['1234567890']);
  
  if ($st->fetch()) {
    echo "⚠️  Persona de ejemplo ya existe. Saltando creación de registro.\n";
  } else {
    // Insertar persona
    $ins = $pdo->prepare(
      "INSERT INTO persons(cedula, nombres, apellidos, celular) VALUES(?, ?, ?, ?)"
    );
    $ins->execute(['1234567890', 'Juan', 'Pérez', '+58 412 1234567']);
    $person_id = (int)$pdo->lastInsertId();

    // Obtener evento creado
    $st = $pdo->prepare("SELECT id FROM events WHERE nombre = ? LIMIT 1");
    $st->execute(['Conferencia de Tecnología 2026']);
    $event = $st->fetch();

    if ($event) {
      $event_id = (int)$event['id'];

      // Insertar registro
      $ins = $pdo->prepare(
        "INSERT INTO registrations(event_id, person_id, status) VALUES(?, ?, 'ACTIVE')"
      );
      $ins->execute([$event_id, $person_id]);
      $registration_id = (int)$pdo->lastInsertId();

      // Crear token QR
      $token = new_token();
      $hash = sha256($token);
      $qr_image = generate_qr_base64($token);

      $ins = $pdo->prepare(
        "INSERT INTO qr_tokens(registration_id, token_hash, qr_image_base64) VALUES(?, ?, ?)"
      );
      $ins->execute([$registration_id, $hash, $qr_image]);

      echo "✅ Registro de ejemplo creado:\n";
      echo "   Persona: Juan Pérez\n";
      echo "   Cédula: 1234567890\n";
      echo "   Evento: Conferencia de Tecnología 2026\n";
      echo "   Token QR generado automáticamente\n\n";
    }
  }

  echo "✨ Seeder completado exitosamente!\n";
  echo "\n📝 Datos de ejemplo:\n";
  echo "   Admin: admin@local / Admin123*\n";
  echo "   Operador: operador@local / Operador123*\n";
  echo "   Evento: Conferencia de Tecnología 2026 (Conferencia)\n";
  echo "   Registro: 1234567890 - Juan Pérez\n";

} catch (Throwable $e) {
  echo "❌ Error: " . $e->getMessage() . "\n";
  exit(1);
}
