<?php
// 8 · Nuevo turno (Belén: reservar.php): paciente, profesional, servicios con el total, fecha y horario libre
require_once "../includes/funciones.php";
require_once "../includes/turnos_funciones.php";
requerir_login('../');

$t = [];
$clientes = $conexion->query("SELECT id_cliente, apellido, nombre, dni FROM clientes WHERE activo = 1 ORDER BY apellido, nombre")->fetchAll();
$profesionales = $conexion->query("
    SELECT p.id_profesional, p.apellido, p.nombre, e.nombre AS especialidad
    FROM profesionales p
    INNER JOIN especialidades e ON e.id_especialidad = p.id_especialidad
    WHERE p.activo = 1
    ORDER BY p.apellido, p.nombre")->fetchAll();
$serviciosDelTurno = [];
$estados = null;   // un turno nuevo siempre arranca Pendiente

$raiz = '../';
$titulo = 'Nuevo turno';
require "../includes/header.php";
?>
<div class="mb-4">
    <h2 class="fw-bold">Nuevo turno</h2>
    <p class="text-muted">Completá los datos para registrar un turno.</p>
</div>
<?php require "_formulario.php"; ?>
<?php require "../includes/footer.php"; ?>
