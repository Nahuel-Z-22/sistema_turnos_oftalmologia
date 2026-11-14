<?php
require_once "../includes/funciones.php";
requerir_login('../');

$buscar = trim($_GET['buscar'] ?? '');
$like = "%$buscar%";

// Pacientes con la cantidad de turnos de cada uno (LEFT JOIN para incluir a los que no tienen)
$q = $conexion->prepare("SELECT c.*, COUNT(t.id_turno) AS cant_turnos
                         FROM clientes c
                         LEFT JOIN turnos t ON t.id_cliente = c.id_cliente
                         WHERE c.apellido LIKE :b1 OR c.nombre LIKE :b2 OR c.dni LIKE :b3
                         GROUP BY c.id_cliente
                         ORDER BY c.apellido, c.nombre");
$q->execute([':b1' => $like, ':b2' => $like, ':b3' => $like]);
$clientes = $q->fetchAll();

$raiz = '../';
$titulo = 'Pacientes';
require "../includes/header.php";
?>
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <h4 class="mb-0">Pacientes</h4>
        <form class="d-flex gap-2" method="GET">
            <input class="form-control" name="buscar" value="<?= e($buscar) ?>" placeholder="Apellido, nombre o DNI">
            <button class="btn btn-outline-secondary">Buscar</button>
        </form>
        <a class="btn btn-primary" href="nuevo.php">+ Nuevo paciente</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                <tr><th>ID</th><th>Apellido y nombre</th><th>DNI</th><th>Teléfono</th><th>Email</th><th>Turnos</th><th>Estado</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                <?php foreach ($clientes as $c) { ?>
                    <tr>
                        <td><?= $c['id_cliente'] ?></td>
                        <td><?= e($c['apellido'] . ', ' . $c['nombre']) ?></td>
                        <td><?= e($c['dni']) ?></td>
                        <td><?= e($c['telefono']) ?></td>
                        <td><?= e($c['email']) ?></td>
                        <td><span class="badge text-bg-secondary"><?= $c['cant_turnos'] ?></span></td>
                        <td><?= $c['activo'] ? '<span class="badge text-bg-success">Activo</span>' : '<span class="badge text-bg-secondary">Inactivo</span>' ?></td>
                        <td class="text-nowrap">
                            <a class="btn btn-warning btn-sm" href="editar.php?id=<?= $c['id_cliente'] ?>">Editar</a>
                            <?= boton_eliminar('eliminar.php', $c['id_cliente'], '¿Eliminar paciente?') ?>
                        </td>
                    </tr>
                <?php } ?>
                <?php if (!$clientes) { ?>
                    <tr><td colspan="8" class="text-center text-muted">No se encontraron pacientes.</td></tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
        <a class="btn btn-secondary" href="../home.php">Inicio</a>
    </div>
</div>
<?php require "../includes/footer.php"; ?>
