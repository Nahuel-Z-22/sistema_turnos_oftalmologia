<?php
require_once "../includes/funciones.php";
requerir_admin('../');

$q = $conexion->prepare("SELECT * FROM usuarios_sistema WHERE id_usuario = :id");
$q->execute([':id' => $_GET['id'] ?? 0]);
$u = $q->fetch();
if (!$u) redirigir('listar.php', 'danger', 'Usuario no encontrado.');

$roles = $conexion->query("SELECT * FROM roles ORDER BY id_rol")->fetchAll();

$raiz = '../';
$titulo = 'Editar usuario';
require "../includes/header.php";
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-warning"><h4 class="mb-0">Editar usuario del sistema</h4></div>
            <div class="card-body p-4">
                <form action="modificar.php" method="POST">
                    <input type="hidden" name="id_usuario" value="<?= $u['id_usuario'] ?>">
                    <?php require "_formulario.php"; ?>
                    <a class="btn btn-secondary" href="listar.php">Cancelar</a>
                    <button class="btn btn-warning">Guardar cambios</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require "../includes/footer.php"; ?>
