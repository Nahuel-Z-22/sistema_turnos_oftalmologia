<?php
// 7 · Turnos registrados (Belén: reservas.php), con filtros y el total de cada turno
require_once "../includes/funciones.php";
require_once "../includes/turnos_funciones.php";
requerir_login('../');

$f = [
    'desde' => $_GET['desde'] ?? '',
    'hasta' => $_GET['hasta'] ?? '',
    'id_profesional' => (int)($_GET['id_profesional'] ?? 0),
    'id_estado' => (int)($_GET['id_estado'] ?? 0),
    'buscar' => trim($_GET['buscar'] ?? ''),
];

// Se arma el WHERE solo con los filtros que se completaron
$where = [];
$params = [];
if (fecha_valida($f['desde'])) {
    $where[] = 't.fecha >= :desde';
    $params[':desde'] = $f['desde'];
}
if (fecha_valida($f['hasta'])) {
    $where[] = 't.fecha <= :hasta';
    $params[':hasta'] = $f['hasta'];
}
if ($f['id_profesional']) {
    $where[] = 't.id_profesional = :prof';
    $params[':prof'] = $f['id_profesional'];
}
if ($f['id_estado']) {
    $where[] = 't.id_estado = :estado';
    $params[':estado'] = $f['id_estado'];
}
if ($f['buscar'] !== '') {
    $where[] = '(c.apellido LIKE :b1 OR c.nombre LIKE :b2 OR c.dni LIKE :b3)';
    $params[':b1'] = $params[':b2'] = $params[':b3'] = '%' . $f['buscar'] . '%';
}

// Cabecera + total calculado del detalle (LEFT JOIN por si un turno no tuviera servicios)
$q = $conexion->prepare("
    SELECT t.id_turno, t.fecha, t.hora_inicio, t.hora_fin, t.id_estado, t.id_usuario,
           c.apellido, c.nombre,
           p.apellido AS prof_apellido, p.nombre AS prof_nombre,
           e.nombre AS estado,
           COALESCE(SUM(d.cantidad * d.precio_unitario), 0) AS total
    FROM turnos t
    INNER JOIN clientes c ON c.id_cliente = t.id_cliente
    INNER JOIN profesionales p ON p.id_profesional = t.id_profesional
    INNER JOIN estados_turno e ON e.id_estado = t.id_estado
    LEFT JOIN detalle_turno d ON d.id_turno = t.id_turno
    " . ($where ? 'WHERE ' . implode(' AND ', $where) : '') . "
    GROUP BY t.id_turno, t.fecha, t.hora_inicio, t.hora_fin, t.id_estado, t.id_usuario,
             c.apellido, c.nombre, p.apellido, p.nombre, e.nombre
    ORDER BY t.fecha DESC, t.hora_inicio DESC");
$q->execute($params);
$turnos = $q->fetchAll();

// Suma de los turnos listados (los cancelados no se cobran)
$totalGeneral = 0;
foreach ($turnos as $t) {
    if ($t['id_estado'] != ESTADO_CANCELADO) {
        $totalGeneral += $t['total'];
    }
}

$profesionales = $conexion->query("SELECT id_profesional, apellido, nombre FROM profesionales ORDER BY apellido, nombre")->fetchAll();
$estados = $conexion->query("SELECT id_estado, nombre FROM estados_turno ORDER BY id_estado")->fetchAll();

$raiz = '../';
$titulo = 'Turnos';
require "../includes/header.php";
?>
<h2 class="mb-2">Turnos registrados</h2>
<p class="text-muted mb-4">Consulta y administración de turnos de la clínica.</p>

<div class="card shadow-sm">
    <div class="card-body">
        <form class="row g-2 align-items-end mb-4" method="GET">
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1" for="desde">Desde</label>
                <input type="date" class="form-control" name="desde" id="desde" value="<?= e($f['desde']) ?>">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1" for="hasta">Hasta</label>
                <input type="date" class="form-control" name="hasta" id="hasta" value="<?= e($f['hasta']) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1" for="id_profesional">Profesional</label>
                <select class="form-select" name="id_profesional" id="id_profesional">
                    <option value="">Todos</option>
                    <?php foreach ($profesionales as $p) { ?>
                        <option value="<?= $p['id_profesional'] ?>" <?= $f['id_profesional'] == $p['id_profesional'] ? 'selected' : '' ?>><?= e($p['apellido'] . ', ' . $p['nombre']) ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1" for="id_estado">Estado</label>
                <select class="form-select" name="id_estado" id="id_estado">
                    <option value="">Todos</option>
                    <?php foreach ($estados as $est) { ?>
                        <option value="<?= $est['id_estado'] ?>" <?= $f['id_estado'] == $est['id_estado'] ? 'selected' : '' ?>><?= e($est['nombre']) ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1" for="buscar">Paciente</label>
                <input class="form-control" name="buscar" id="buscar" value="<?= e($f['buscar']) ?>" placeholder="Apellido, nombre o DNI">
            </div>
            <div class="col-12 d-flex flex-wrap gap-2">
                <button class="btn btn-borde">Filtrar</button>
                <a class="btn btn-link" href="listar.php">Limpiar filtros</a>
                <a class="btn btn-link" href="listar.php?desde=<?= date('Y-m-d') ?>&hasta=<?= date('Y-m-d') ?>">Solo hoy</a>
                <a class="btn btn-verde ms-auto" href="nuevo.php">+ Nuevo turno</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                <tr><th>N°</th><th>Paciente</th><th>Profesional</th><th>Fecha</th><th>Horario</th><th>Estado</th><th class="text-end">Total</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                <?php foreach ($turnos as $t) { ?>
                    <tr class="<?= $t['id_estado'] == ESTADO_CANCELADO ? 'text-muted' : '' ?>">
                        <td><?= $t['id_turno'] ?></td>
                        <td>
                            <?= e($t['apellido'] . ', ' . $t['nombre']) ?>
                            <?php if (!$t['id_usuario']) { ?><span class="badge text-bg-info" title="Reservado por el paciente desde la web">Web</span><?php } ?>
                        </td>
                        <td><?= e($t['prof_apellido'] . ', ' . $t['prof_nombre']) ?></td>
                        <td><?= date('d/m/Y', strtotime($t['fecha'])) ?></td>
                        <td><?= substr($t['hora_inicio'], 0, 5) ?> a <?= substr($t['hora_fin'], 0, 5) ?></td>
                        <td><?= badge_estado($t['id_estado'], $t['estado']) ?></td>
                        <td class="text-end"><?= plata($t['total']) ?></td>
                        <td class="text-nowrap">
                            <a class="btn btn-verde btn-sm" href="ver.php?id=<?= $t['id_turno'] ?>">Ver detalle</a>
                            <a class="btn btn-warning btn-sm" href="editar.php?id=<?= $t['id_turno'] ?>">Editar</a>
                            <?php if ($t['id_estado'] != ESTADO_CANCELADO) { ?>
                                <form action="cancelar.php" method="POST" class="d-inline" onsubmit="return confirm('¿Cancelar el turno N° <?= $t['id_turno'] ?>?')">
                                    <?= csrf_input() ?>
                                    <input type="hidden" name="id" value="<?= $t['id_turno'] ?>">
                                    <button class="btn btn-danger btn-sm">Cancelar</button>
                                </form>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
                <?php if (!$turnos) { ?>
                    <tr><td colspan="8" class="text-center text-muted">No hay turnos registrados.</td></tr>
                <?php } ?>
                </tbody>
                <?php if ($turnos) { ?>
                    <tfoot>
                    <tr class="fw-bold">
                        <td colspan="6" class="text-end"><?= count($turnos) ?> turno(s) · Total sin cancelados</td>
                        <td class="text-end"><?= plata($totalGeneral) ?></td>
                        <td></td>
                    </tr>
                    </tfoot>
                <?php } ?>
            </table>
        </div>
        <a class="btn btn-outline-secondary" href="../home.php">Volver</a>
    </div>
</div>
<?php require "../includes/footer.php"; ?>
