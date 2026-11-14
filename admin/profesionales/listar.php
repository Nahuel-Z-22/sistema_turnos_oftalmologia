<?php
require_once "../includes/funciones.php";
requerir_login('../');

// INNER JOIN con especialidades y conteo de servicios que realiza cada profesional
$profesionales = $conexion->query("
    SELECT p.id_profesional, p.apellido, p.nombre, p.matricula, p.telefono, p.activo,
           e.nombre AS especialidad, COUNT(ps.id_servicio) AS cant_servicios
    FROM profesionales p
    INNER JOIN especialidades e ON e.id_especialidad = p.id_especialidad
    LEFT JOIN profesional_servicio ps ON ps.id_profesional = p.id_profesional
    GROUP BY p.id_profesional, p.apellido, p.nombre, p.matricula, p.telefono, p.activo, e.nombre
    ORDER BY p.apellido, p.nombre
")->fetchAll();

$raiz = '../';
$titulo = 'Profesionales';
require "../includes/header.php";
?>
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Profesionales</h4>
        <a href="nuevo.php" class="btn btn-primary">+ Nuevo profesional</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                <tr><th>ID</th><th>Apellido y nombre</th><th>Especialidad</th><th>Matrícula</th><th>Teléfono</th><th>Servicios</th><th>Estado</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                <?php foreach ($profesionales as $p) { ?>
                    <tr>
                        <td><?= $p['id_profesional'] ?></td>
                        <td><?= e($p['apellido'] . ', ' . $p['nombre']) ?></td>
                        <td><?= e($p['especialidad']) ?></td>
                        <td><?= e($p['matricula']) ?></td>
                        <td><?= e($p['telefono']) ?></td>
                        <td><span class="badge text-bg-secondary"><?= $p['cant_servicios'] ?></span></td>
                        <td><?= $p['activo'] ? '<span class="badge text-bg-success">Activo</span>' : '<span class="badge text-bg-secondary">Inactivo</span>' ?></td>
                        <td class="text-nowrap">
                            <a class="btn btn-warning btn-sm" href="editar.php?id=<?= $p['id_profesional'] ?>">Editar</a>
                            <?= boton_eliminar('eliminar.php', $p['id_profesional'], '¿Eliminar profesional?') ?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
        <a href="../home.php" class="btn btn-secondary">Inicio</a>
    </div>
</div>
<?php require "../includes/footer.php"; ?>
