<?php
require_once "../includes/funciones.php";
requerir_admin('../');

// INNER JOIN con roles para mostrar el nombre del rol
$usuarios = $conexion->query("SELECT u.*, r.nombre AS rol
                              FROM usuarios_sistema u
                              INNER JOIN roles r ON r.id_rol = u.id_rol
                              ORDER BY u.usuario")->fetchAll();

$raiz = '../';
$titulo = 'Usuarios del sistema';
require "../includes/header.php";
?>
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Usuarios del sistema (login)</h4>
        <a class="btn btn-primary" href="nuevo.php">+ Nuevo usuario</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                <tr><th>ID</th><th>Usuario</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Estado</th><th>Último acceso</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                <?php foreach ($usuarios as $u) { ?>
                    <tr>
                        <td><?= $u['id_usuario'] ?></td>
                        <td><?= e($u['usuario']) ?></td>
                        <td><?= e($u['nombre_completo']) ?></td>
                        <td><?= e($u['email']) ?></td>
                        <td><span class="badge text-bg-<?= $u['rol'] === ROL_ADMIN ? 'danger' : 'primary' ?>"><?= e($u['rol']) ?></span></td>
                        <td><?= $u['activo'] ? '<span class="badge text-bg-success">Activo</span>' : '<span class="badge text-bg-secondary">Inactivo</span>' ?></td>
                        <td><?= $u['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($u['ultimo_acceso'])) : '—' ?></td>
                        <td class="text-nowrap">
                            <a class="btn btn-warning btn-sm" href="editar.php?id=<?= $u['id_usuario'] ?>">Editar</a>
                            <?php if ($u['id_usuario'] != $_SESSION['id_usuario']) { ?>
                                <?= boton_eliminar('eliminar.php', $u['id_usuario'], '¿Eliminar usuario?') ?>
                            <?php } ?>
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
