<?php
// Guarda un turno nuevo (Belén: guardar_turno.php): cabecera + detalle en una transacción
require_once "../includes/funciones.php";
require_once "../includes/turnos_funciones.php";
requerir_login('../');
verificar_post();

$idCliente = (int)($_POST['id_cliente'] ?? 0);
$idProfesional = (int)($_POST['id_profesional'] ?? 0);
$fecha = $_POST['fecha'] ?? '';
$hora = $_POST['hora_inicio'] ?? '';

// Solo se aceptan servicios activos que haga ese profesional (precio y duración salen de la base)
$servicios = servicios_del_profesional($conexion, $idProfesional, $_POST['servicios'] ?? []);

if (!$idCliente || !$idProfesional) {
    redirigir('nuevo.php', 'danger', 'Elegí el paciente y el profesional.');
}
if (!$servicios) {
    redirigir('nuevo.php', 'danger', 'Tenés que seleccionar al menos un servicio.');
}
if (!fecha_valida($fecha) || $fecha < date('Y-m-d')) {
    redirigir('nuevo.php', 'danger', 'La fecha no es válida.');
}

$duracion = duracion_total($servicios);

try {
    $conexion->beginTransaction();

    // Se vuelve a controlar que el horario siga libre (otro usuario o un paciente pudo tomarlo mientras tanto).
    // bloquear_agenda() hace que dos turnos simultáneos con el mismo profesional se guarden de a uno.
    bloquear_agenda($conexion, $idProfesional);
    if (!in_array($hora, horarios_libres($conexion, $idProfesional, $fecha, $duracion), true)) {
        $conexion->rollBack();
        redirigir('nuevo.php', 'danger', 'El horario elegido ya no está disponible. Elegí otro.');
    }

    $q = $conexion->prepare("INSERT INTO turnos (id_cliente, id_usuario, id_profesional, id_estado, fecha, hora_inicio, hora_fin, observaciones)
                             VALUES (:cliente, :usuario, :profesional, :estado, :fecha, :inicio, :fin, :obs)");
    $q->execute([
        ':cliente' => $idCliente,
        ':usuario' => $_SESSION['id_usuario'],   // quién cargó el turno
        ':profesional' => $idProfesional,
        ':estado' => ESTADO_PENDIENTE,
        ':fecha' => $fecha,
        ':inicio' => $hora,
        ':fin' => sumar_minutos($hora, $duracion),
        ':obs' => valor_o_null($_POST['observaciones'] ?? null),
    ]);
    $idTurno = $conexion->lastInsertId();

    // Detalle: el precio se copia del servicio en este momento
    $qd = $conexion->prepare("INSERT INTO detalle_turno (id_turno, id_servicio, cantidad, precio_unitario)
                              VALUES (:turno, :servicio, 1, :precio)");
    foreach ($servicios as $s) {
        $qd->execute([':turno' => $idTurno, ':servicio' => $s['id_servicio'], ':precio' => $s['precio']]);
    }

    $conexion->commit();
    redirigir("ver.php?id=$idTurno", 'success', "Turno N° $idTurno guardado.");
} catch (PDOException $e) {
    $conexion->rollBack();
    redirigir('nuevo.php', 'danger', mensaje_error_bd($e));
}
