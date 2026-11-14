<?php
// 10 · Modificar turno (Belén: modificar.php): paciente, profesional, servicios, fecha, horario y estado
require_once "../includes/funciones.php";
require_once "../includes/turnos_funciones.php";
requerir_login('../');

$q = $conexion->prepare("SELECT * FROM turnos WHERE id_turno = :id");
$q->execute([':id' => $_GET['id'] ?? 0]);
$t = $q->fetch();
if (!$t) redirigir('listar.php', 'danger', 'No se encontró el turno.');

// Se incluye al paciente/profesional actual aunque esté inactivo, para no perderlo al guardar
$qc = $conexion->prepare("SELECT id_cliente, apellido, nombre, dni FROM clientes WHERE activo = 1 OR id_cliente = :id ORDER BY apellido, nombre");
$qc->execute([':id' => $t['id_cliente']]);
$clientes = $qc->fetchAll();

$qp = $conexion->prepare("
    SELECT p.id_profesional, p.apellido, p.nombre, e.nombre AS especialidad
    FROM profesionales p
    INNER JOIN especialidades e ON e.id_especialidad = p.id_especialidad
    WHERE p.activo = 1 OR p.id_profesional = :id
    ORDER BY p.apellido, p.nombre");
$qp->execute([':id' => $t['id_profesional']]);
$profesionales = $qp->fetchAll();

// Servicios del turno con el precio con que se sacó (al guardar se conserva ese precio)
$qs = $conexion->prepare("SELECT id_servicio, precio_unitario FROM detalle_turno WHERE id_turno = :id");
$qs->execute([':id' => $t['id_turno']]);
$preciosGuardados = $qs->fetchAll(PDO::FETCH_KEY_PAIR);
$serviciosDelTurno = array_keys($preciosGuardados);

$estados = $conexion->query("SELECT id_estado, nombre FROM estados_turno ORDER BY id_estado")->fetchAll();

$raiz = '../';
$titulo = 'Modificar turno';
require "../includes/header.php";
?>
<div class="mb-4">
    <h2 class="fw-bold">Modificar turno N° <?= $t['id_turno'] ?></h2>
    <p class="text-muted">Editá los datos de la reserva.</p>
</div>
<?php require "_formulario.php"; ?>
<?php require "../includes/footer.php"; ?>
