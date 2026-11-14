<?php
// Panel principal (solo usuarios logueados)
require_once "includes/funciones.php";
requerir_login();

$hoy = date('Y-m-d');

// Estados de la tabla estados_turno: 1 Pendiente, 2 Confirmado, 3 Atendido, 4 Cancelado
// Color de Bootstrap para cada estado (el texto siempre se muestra al lado)
$colorEstado = [1 => 'warning', 2 => 'primary', 3 => 'success', 4 => 'secondary'];

// Contadores para el panel
$qHoy = $conexion->prepare("SELECT COUNT(*) FROM turnos WHERE fecha = :hoy AND id_estado <> 4");
$qHoy->execute([':hoy' => $hoy]);

$totales = [
    'hoy' => $qHoy->fetchColumn(),
    'pendientes' => $conexion->query("SELECT COUNT(*) FROM turnos WHERE id_estado = 1")->fetchColumn(),
    'clientes' => $conexion->query("SELECT COUNT(*) FROM clientes WHERE activo = 1")->fetchColumn(),
    'profesionales' => $conexion->query("SELECT COUNT(*) FROM profesionales WHERE activo = 1")->fetchColumn(),
    'servicios' => $conexion->query("SELECT COUNT(*) FROM servicios WHERE activo = 1")->fetchColumn(),
];

$modulos = [
    ['Turnos', 'Registrar, consultar, modificar y cancelar turnos.', 'turnos/listar.php', $totales['pendientes'] . ' pendientes de confirmar'],
    ['Pacientes', 'Personas que se atienden en la clínica.', 'clientes/listar.php', $totales['clientes'] . ' pacientes activos'],
    ['Profesionales', 'Oftalmólogos, su especialidad y sus servicios.', 'profesionales/listar.php', $totales['profesionales'] . ' profesionales activos'],
    ['Servicios', 'Consultas y estudios, con precio y duración.', 'servicios/listar.php', $totales['servicios'] . ' servicios activos'],
];
if (es_admin()) {
    $modulos[] = ['Usuarios del sistema', 'Quién puede ingresar al sistema (login).', 'usuarios_sistema/listar.php', 'Solo administradores'];
}

// Turnos de hoy (INNER JOIN con paciente, profesional y estado)
$q = $conexion->prepare("
    SELECT t.id_turno, t.hora_inicio, t.id_estado, c.apellido, c.nombre,
           p.apellido AS prof_apellido, p.nombre AS prof_nombre, e.nombre AS estado
    FROM turnos t
    INNER JOIN clientes c ON c.id_cliente = t.id_cliente
    INNER JOIN profesionales p ON p.id_profesional = t.id_profesional
    INNER JOIN estados_turno e ON e.id_estado = t.id_estado
    WHERE t.fecha = :hoy
    ORDER BY t.hora_inicio");
$q->execute([':hoy' => $hoy]);
$turnosHoy = $q->fetchAll();

$raiz = '';
$titulo = 'Inicio';
require "includes/header.php";
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="mb-1">Bienvenido, <?= e($_SESSION['nombre']) ?></h1>
        <p class="text-muted mb-0">Hoy es <?= date('d/m/Y') ?> · <?= $totales['hoy'] ?> turno(s) para hoy</p>
    </div>
    <a class="btn btn-verde btn-lg" href="turnos/nuevo.php">+ Nuevo turno</a>
</div>

<div class="row g-4 mb-4">
    <?php foreach ($modulos as $m) { ?>
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <h4><?= e($m[0]) ?></h4>
                    <p class="mb-1"><?= e($m[1]) ?></p>
                    <p class="text-muted small"><?= e($m[3]) ?></p>
                    <a class="btn btn-primary mt-auto align-self-start" href="<?= e($m[2]) ?>">Administrar</a>
                </div>
            </div>
        </div>
    <?php } ?>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white"><h4 class="mb-0">Turnos de hoy</h4></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                <tr><th>Hora</th><th>Paciente</th><th>Profesional</th><th>Estado</th><th></th></tr>
                </thead>
                <tbody>
                <?php foreach ($turnosHoy as $t) { ?>
                    <tr>
                        <td><?= substr($t['hora_inicio'], 0, 5) ?></td>
                        <td><?= e($t['apellido'] . ', ' . $t['nombre']) ?></td>
                        <td><?= e($t['prof_apellido'] . ', ' . $t['prof_nombre']) ?></td>
                        <td><span class="badge text-bg-<?= $colorEstado[$t['id_estado']] ?? 'light' ?>"><?= e($t['estado']) ?></span></td>
                        <td><a class="btn btn-info btn-sm" href="turnos/ver.php?id=<?= $t['id_turno'] ?>">Ver</a></td>
                    </tr>
                <?php } ?>
                <?php if (!$turnosHoy) { ?>
                    <tr><td colspan="5" class="text-center text-muted">No hay turnos para hoy.</td></tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require "includes/footer.php"; ?>
