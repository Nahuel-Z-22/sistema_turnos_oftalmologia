<?php
// 9 · Detalle del turno (Belén: detalle.php): cabecera + servicios con subtotal y total
require_once "../includes/funciones.php";
require_once "../includes/turnos_funciones.php";
requerir_login('../');

$q = $conexion->prepare("
    SELECT t.*, c.apellido, c.nombre, c.dni, c.telefono, c.email,
           p.apellido AS prof_apellido, p.nombre AS prof_nombre, esp.nombre AS especialidad,
           e.nombre AS estado, u.nombre_completo AS cargado_por
    FROM turnos t
    INNER JOIN clientes c ON c.id_cliente = t.id_cliente
    INNER JOIN profesionales p ON p.id_profesional = t.id_profesional
    INNER JOIN especialidades esp ON esp.id_especialidad = p.id_especialidad
    INNER JOIN estados_turno e ON e.id_estado = t.id_estado
    LEFT JOIN usuarios_sistema u ON u.id_usuario = t.id_usuario
    WHERE t.id_turno = :id");
$q->execute([':id' => $_GET['id'] ?? 0]);
$t = $q->fetch();
if (!$t) redirigir('listar.php', 'danger', 'No se encontró el turno.');

$qd = $conexion->prepare("
    SELECT s.nombre, d.cantidad, d.precio_unitario, d.cantidad * d.precio_unitario AS subtotal
    FROM detalle_turno d
    INNER JOIN servicios s ON s.id_servicio = d.id_servicio
    WHERE d.id_turno = :id
    ORDER BY s.nombre");
$qd->execute([':id' => $t['id_turno']]);
$detalle = $qd->fetchAll();

$total = array_sum(array_column($detalle, 'subtotal'));

$raiz = '../';
$titulo = 'Turno N° ' . $t['id_turno'];
require "../includes/header.php";
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h2 class="mb-1">Detalle del turno N° <?= $t['id_turno'] ?> <?= badge_estado($t['id_estado'], $t['estado']) ?></h2>
        <p class="text-muted mb-0">
            Registrado el <?= date('d/m/Y H:i', strtotime($t['fecha_registro'])) ?>
            por <?= $t['cargado_por'] ? e($t['cargado_por']) : 'el paciente (reserva online)' ?>
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-verde" href="editar.php?id=<?= $t['id_turno'] ?>">Modificar turno</a>
        <?php if ($t['id_estado'] != ESTADO_CANCELADO) { ?>
            <form action="cancelar.php" method="POST" class="d-flex gap-2" onsubmit="return confirm('¿Estás segura/o de cancelar este turno? El horario queda libre.')">
                <?= csrf_input() ?>
                <input type="hidden" name="id" value="<?= $t['id_turno'] ?>">
                <input class="form-control" name="motivo" maxlength="150" placeholder="Motivo (opcional)">
                <button class="btn btn-danger text-nowrap">Cancelar turno</button>
            </form>
        <?php } ?>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h5 class="mb-3">Datos del turno</h5>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Fecha</dt>
                    <dd class="col-sm-8"><?= date('d/m/Y', strtotime($t['fecha'])) ?></dd>
                    <dt class="col-sm-4">Horario</dt>
                    <dd class="col-sm-8"><?= substr($t['hora_inicio'], 0, 5) ?> a <?= substr($t['hora_fin'], 0, 5) ?></dd>
                    <dt class="col-sm-4">Paciente</dt>
                    <dd class="col-sm-8"><?= e($t['apellido'] . ', ' . $t['nombre']) ?><br><small class="text-muted">DNI <?= e($t['dni']) ?></small></dd>
                    <dt class="col-sm-4">Contacto</dt>
                    <dd class="col-sm-8"><?= e($t['telefono'] ?: '-') ?><br><small class="text-muted"><?= e($t['email'] ?: '') ?></small></dd>
                    <dt class="col-sm-4">Profesional</dt>
                    <dd class="col-sm-8"><?= e($t['prof_apellido'] . ', ' . $t['prof_nombre']) ?><br><small class="text-muted"><?= e($t['especialidad']) ?></small></dd>
                    <dt class="col-sm-4">Observaciones</dt>
                    <dd class="col-sm-8 mb-0"><?= e($t['observaciones'] ?: 'Sin observaciones') ?></dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h5 class="mb-3">Servicios del turno</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                        <tr><th>Servicio</th><th class="text-center">Cantidad</th><th class="text-end">Precio</th><th class="text-end">Subtotal</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($detalle as $d) { ?>
                            <tr>
                                <td><?= e($d['nombre']) ?></td>
                                <td class="text-center"><?= $d['cantidad'] ?></td>
                                <td class="text-end"><?= plata($d['precio_unitario']) ?></td>
                                <td class="text-end"><?= plata($d['subtotal']) ?></td>
                            </tr>
                        <?php } ?>
                        <?php if (!$detalle) { ?>
                            <tr><td colspan="4" class="text-center text-muted">No hay servicios registrados.</td></tr>
                        <?php } ?>
                        </tbody>
                        <tfoot>
                        <tr class="fw-bold"><td colspan="3" class="text-end">Total</td><td class="text-end"><?= plata($total) ?></td></tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<a class="btn btn-outline-secondary mt-4" href="listar.php">Volver a turnos</a>
<?php require "../includes/footer.php"; ?>
