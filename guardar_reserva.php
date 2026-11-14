<?php
// Reserva online · Guarda paciente (si es nuevo) + turno + detalle en una transacción (Sofía + Belén)
require_once "admin/includes/funciones.php";
require_once "admin/includes/turnos_funciones.php";
verificar_post();

$idProfesional = (int)($_POST['id_profesional'] ?? 0);
$fecha = $_POST['fecha'] ?? '';
$hora = $_POST['hora'] ?? '';

$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$dni = trim($_POST['dni'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$email = valor_o_null($_POST['email'] ?? null);

$volver = 'datos.php?' . http_build_query(['id_profesional' => $idProfesional, 'fecha' => $fecha, 'hora' => $hora]);

// Validaciones de los datos del paciente
$error = '';
if ($nombre === '' || $apellido === '' || $telefono === '') {
    $error = 'Completá nombre, apellido y teléfono.';
} elseif (!preg_match('/^[0-9]{6,15}$/', $dni)) {
    $error = 'El DNI tiene que tener solo números (sin puntos).';
} elseif ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'El correo electrónico no es válido.';
}
if ($error) {
    $_SESSION['datos_reserva'] = $_POST;   // para no tener que escribir todo de nuevo
    redirigir($volver, 'danger', $error);
}

// El profesional tiene que atender la Consulta
$consulta = servicios_del_profesional($conexion, $idProfesional, [SERVICIO_CONSULTA]);
$esReservable = false;
foreach (profesionales_para_reserva($conexion) as $p) {
    if ($p['id_profesional'] == $idProfesional) {
        $esReservable = true;
    }
}
if (!$consulta || !$esReservable) {
    redirigir('reservar.php', 'danger', 'Elegí un profesional.');
}
$duracion = duracion_total($consulta);

try {
    $conexion->beginTransaction();

    // Se controla otra vez el horario dentro de la transacción (otra persona pudo reservarlo recién).
    // bloquear_agenda() hace que dos reservas simultáneas con el mismo profesional se guarden de a una.
    bloquear_agenda($conexion, $idProfesional);
    if (!in_array($hora, horarios_libres($conexion, $idProfesional, $fecha, $duracion), true)) {
        $conexion->rollBack();
        redirigir('reservar.php?' . http_build_query(['id_profesional' => $idProfesional, 'fecha' => $fecha]),
            'danger', 'Ese horario se acaba de ocupar. Elegí otro.');
    }

    // Paciente: si el DNI ya existe se usa ese paciente; si no, se crea.
    // A un paciente existente solo se le completan el teléfono o el email si no los tenía
    // (así nadie puede cambiar los datos de otro escribiendo su DNI).
    $q = $conexion->prepare("SELECT id_cliente FROM clientes WHERE dni = :dni");
    $q->execute([':dni' => $dni]);
    $idCliente = $q->fetchColumn();

    if ($idCliente) {
        $conexion->prepare("UPDATE clientes
                            SET telefono = COALESCE(NULLIF(telefono, ''), :tel),
                                email = COALESCE(NULLIF(email, ''), :email),
                                activo = 1
                            WHERE id_cliente = :id")
                 ->execute([':tel' => $telefono, ':email' => $email, ':id' => $idCliente]);
    } else {
        $conexion->prepare("INSERT INTO clientes (nombre, apellido, dni, telefono, email) VALUES (:nombre, :apellido, :dni, :tel, :email)")
                 ->execute([':nombre' => $nombre, ':apellido' => $apellido, ':dni' => $dni, ':tel' => $telefono, ':email' => $email]);
        $idCliente = $conexion->lastInsertId();
    }

    // Turno: id_usuario NULL (lo reservó el paciente) y estado 1 (Pendiente)
    $q = $conexion->prepare("INSERT INTO turnos (id_cliente, id_usuario, id_profesional, id_estado, fecha, hora_inicio, hora_fin, observaciones)
                             VALUES (:cliente, NULL, :profesional, :estado, :fecha, :inicio, :fin, 'Reserva online')");
    $q->execute([
        ':cliente' => $idCliente,
        ':profesional' => $idProfesional,
        ':estado' => ESTADO_PENDIENTE,
        ':fecha' => $fecha,
        ':inicio' => $hora,
        ':fin' => sumar_minutos($hora, $duracion),
    ]);
    $idTurno = $conexion->lastInsertId();

    // Detalle: la Consulta con el precio de hoy
    $conexion->prepare("INSERT INTO detalle_turno (id_turno, id_servicio, cantidad, precio_unitario) VALUES (:turno, :servicio, 1, :precio)")
             ->execute([':turno' => $idTurno, ':servicio' => SERVICIO_CONSULTA, ':precio' => $consulta[0]['precio']]);

    $conexion->commit();

    // La confirmación se muestra solo a quien hizo la reserva (no se pasa el número por la URL)
    $_SESSION['reserva_ok'] = (int)$idTurno;
    redirigir('reserva_ok.php');
} catch (PDOException $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }
    $_SESSION['datos_reserva'] = $_POST;
    redirigir($volver, 'danger', 'No se pudo guardar la reserva. Intentá de nuevo.');
}
