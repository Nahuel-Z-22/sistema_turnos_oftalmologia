<?php
// El UPDATE de editar.php (Belén: actualizar_turno.php): cabecera + detalle en una transacción
require_once "../includes/funciones.php";
require_once "../includes/turnos_funciones.php";
requerir_login('../');
verificar_post();

$id = (int)($_POST['id_turno'] ?? 0);
$volver = "editar.php?id=$id";

$q = $conexion->prepare("SELECT * FROM turnos WHERE id_turno = :id");
$q->execute([':id' => $id]);
$actual = $q->fetch();
if (!$actual) redirigir('listar.php', 'danger', 'No se encontró el turno.');

$idCliente = (int)($_POST['id_cliente'] ?? 0);
$idProfesional = (int)($_POST['id_profesional'] ?? 0);
$idEstado = (int)($_POST['id_estado'] ?? 0);
$fecha = $_POST['fecha'] ?? '';
$hora = $_POST['hora_inicio'] ?? '';

$servicios = servicios_del_profesional($conexion, $idProfesional, $_POST['servicios'] ?? []);

if (!$idCliente || !$idProfesional) {
    redirigir($volver, 'danger', 'Elegí el paciente y el profesional.');
}
if (!$servicios) {
    redirigir($volver, 'danger', 'Seleccioná al menos un servicio.');
}
if (!array_key_exists($idEstado, COLOR_ESTADO)) {
    redirigir($volver, 'danger', 'El estado no es válido.');
}
if (!fecha_valida($fecha) || !preg_match('/^\d{2}:\d{2}$/', $hora)) {
    redirigir($volver, 'danger', 'Seleccioná una fecha y un horario.');
}

$duracion = duracion_total($servicios);
$horaFin = sumar_minutos($hora, $duracion);

// Si cambió el día, la hora, el profesional o la duración, se controla que el nuevo horario esté libre.
// (Si solo cambia el estado o las observaciones no hace falta: así se puede marcar "Atendido" un turno pasado.)
$cambioAgenda = $fecha !== $actual['fecha']
    || $hora !== substr($actual['hora_inicio'], 0, 5)
    || $horaFin !== substr($actual['hora_fin'], 0, 5)
    || $idProfesional !== (int)$actual['id_profesional'];

// Reactivar un turno cancelado: mientras estuvo cancelado otro paciente pudo tomar ese horario
$reactiva = $actual['id_estado'] == ESTADO_CANCELADO && $idEstado != ESTADO_CANCELADO;

// Si el turno queda cancelado no ocupa horario, así que no hace falta controlar nada
$controlarHorario = ($cambioAgenda || $reactiva) && $idEstado != ESTADO_CANCELADO;

if ($controlarHorario && $cambioAgenda && $fecha < date('Y-m-d')) {
    redirigir($volver, 'danger', 'No se puede mover un turno a una fecha pasada.');
}

try {
    $conexion->beginTransaction();

    if ($controlarHorario) {
        // Mismo control que al guardar: se bloquea la agenda del profesional y se mira si el horario sigue libre
        bloquear_agenda($conexion, $idProfesional);
        $libres = horarios_libres($conexion, $idProfesional, $fecha, $duracion, $id);
        if (!in_array($hora, $libres, true)) {
            $conexion->rollBack();
            redirigir($volver, 'danger', $reactiva && !$cambioAgenda
                ? 'No se puede reactivar el turno: ese horario ya lo tomó otro paciente. Cambiale el día o la hora.'
                : 'El horario elegido no está disponible. Elegí otro.');
        }
    }

    $q = $conexion->prepare("UPDATE turnos
                             SET id_cliente = :cliente, id_profesional = :profesional, id_estado = :estado,
                                 fecha = :fecha, hora_inicio = :inicio, hora_fin = :fin, observaciones = :obs
                             WHERE id_turno = :id");
    $q->execute([
        ':cliente' => $idCliente,
        ':profesional' => $idProfesional,
        ':estado' => $idEstado,
        ':fecha' => $fecha,
        ':inicio' => $hora,
        ':fin' => $horaFin,
        ':obs' => valor_o_null($_POST['observaciones'] ?? null),
        ':id' => $id,
    ]);

    // Los servicios que ya estaban conservan el precio con el que se sacó el turno;
    // los que se agregan toman el precio actual.
    $qp = $conexion->prepare("SELECT id_servicio, precio_unitario FROM detalle_turno WHERE id_turno = :id");
    $qp->execute([':id' => $id]);
    $preciosAnteriores = $qp->fetchAll(PDO::FETCH_KEY_PAIR);

    $conexion->prepare("DELETE FROM detalle_turno WHERE id_turno = :id")->execute([':id' => $id]);
    $qd = $conexion->prepare("INSERT INTO detalle_turno (id_turno, id_servicio, cantidad, precio_unitario)
                              VALUES (:turno, :servicio, 1, :precio)");
    foreach ($servicios as $s) {
        $qd->execute([
            ':turno' => $id,
            ':servicio' => $s['id_servicio'],
            ':precio' => $preciosAnteriores[$s['id_servicio']] ?? $s['precio'],
        ]);
    }

    $conexion->commit();
    redirigir("ver.php?id=$id", 'success', 'Turno actualizado.');
} catch (PDOException $e) {
    $conexion->rollBack();
    redirigir($volver, 'danger', mensaje_error_bd($e));
}
