<?php
// Cancela un turno (Belén: cancelar_turno.php): pasa a estado 4 (Cancelado) y libera el horario.
// No se borra: queda en el historial.
require_once "../includes/funciones.php";
require_once "../includes/turnos_funciones.php";
requerir_login('../');
verificar_post();

$id = (int)($_POST['id'] ?? 0);
$motivo = trim($_POST['motivo'] ?? '');

$q = $conexion->prepare("SELECT id_estado, observaciones FROM turnos WHERE id_turno = :id");
$q->execute([':id' => $id]);
$t = $q->fetch();

if (!$t || $t['id_estado'] == ESTADO_CANCELADO) {
    redirigir('listar.php', 'warning', 'El turno no existe o ya estaba cancelado.');
}

// Si se escribió un motivo, se agrega a las observaciones
$observaciones = $t['observaciones'];
if ($motivo !== '') {
    $observaciones = mb_substr(($observaciones ? $observaciones . ' · ' : '') . 'Cancelado: ' . $motivo, 0, 255);
}

$conexion->prepare("UPDATE turnos SET id_estado = :estado, observaciones = :obs WHERE id_turno = :id")
         ->execute([':estado' => ESTADO_CANCELADO, ':obs' => $observaciones, ':id' => $id]);

redirigir("ver.php?id=$id", 'success', 'Turno cancelado. El horario quedó libre.');
