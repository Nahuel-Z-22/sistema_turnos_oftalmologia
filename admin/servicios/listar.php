<?php
require_once "../includes/funciones.php";
requerir_login('../');

// Cantidad de profesionales que hacen cada servicio (LEFT JOIN para mostrar también los que no tienen)
$servicios = $conexion->query("
    SELECT s.id_servicio, s.nombre, s.descripcion, s.precio, s.duracion_min, s.activo,
           COUNT(ps.id_profesional) AS cant_profesionales
    FROM servicios s
    LEFT JOIN profesional_servicio ps ON ps.id_servicio = s.id_servicio
    GROUP BY s.id_servicio, s.nombre, s.descripcion, s.precio, s.duracion_min, s.activo
    ORDER BY s.nombre
")->fetchAll();

$raiz = '../';
$titulo = 'Servicios';
require "../includes/header.php";
?>
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Servicios</h4>
        <a class="btn btn-primary" href="nuevo.php">+ Nuevo servicio</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                <tr><th>ID</th><th>Servicio</th><th>Descripción</th><th>Minutos</th><th>Precio</th><th>Profesionales</th><th>Estado</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                <?php foreach ($servicios as $s) { ?>
                    <tr>
                        <td><?= $s['id_servicio'] ?></td>
                        <td><?= e($s['nombre']) ?></td>
                        <td><?= e($s['descripcion']) ?></td>
                        <td><?= $s['duracion_min'] ?></td>
                        <td class="text-nowrap"><?= plata($s['precio']) ?></td>
                        <td><span class="badge text-bg-secondary"><?= $s['cant_profesionales'] ?></span></td>
                        <td><?= $s['activo'] ? '<span class="badge text-bg-success">Activo</span>' : '<span class="badge text-bg-secondary">Inactivo</span>' ?></td>
                        <td class="text-nowrap">
                            <a class="btn btn-warning btn-sm" href="editar.php?id=<?= $s['id_servicio'] ?>">Editar</a>
                            <?= boton_eliminar('eliminar.php', $s['id_servicio'], '¿Eliminar servicio?') ?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
        <a class="btn btn-secondary" href="../home.php">Inicio</a>
    </div>
</div>
<?php require "../includes/footer.php"; ?>
