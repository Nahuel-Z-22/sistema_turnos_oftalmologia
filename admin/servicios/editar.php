<?php
require_once "../includes/funciones.php";
requerir_login('../');

$q = $conexion->prepare("SELECT * FROM servicios WHERE id_servicio = :id");
$q->execute([':id' => $_GET['id'] ?? 0]);
$s = $q->fetch();
if (!$s) redirigir('listar.php', 'danger', 'Servicio no encontrado.');

$raiz = '../';
$titulo = 'Editar servicio';
require "../includes/header.php";
?>
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header bg-warning"><h4 class="mb-0">Editar servicio</h4></div>
            <div class="card-body">
                <form action="modificar.php" method="POST">
                    <input type="hidden" name="id_servicio" value="<?= $s['id_servicio'] ?>">
                    <?php require "_formulario.php"; ?>
                    <a class="btn btn-secondary" href="listar.php">Cancelar</a>
                    <button class="btn btn-warning">Guardar cambios</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require "../includes/footer.php"; ?>
