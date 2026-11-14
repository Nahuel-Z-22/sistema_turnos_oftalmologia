<?php
require_once "../includes/funciones.php";
requerir_admin('../');

$u = [];
$roles = $conexion->query("SELECT * FROM roles ORDER BY id_rol")->fetchAll();

$raiz = '../';
$titulo = 'Nuevo usuario';
require "../includes/header.php";
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white"><h4 class="mb-0">Nuevo usuario del sistema</h4></div>
            <div class="card-body p-4">
                <form action="guardar.php" method="POST">
                    <?php require "_formulario.php"; ?>
                    <a class="btn btn-secondary" href="listar.php">Volver</a>
                    <button class="btn btn-success">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require "../includes/footer.php"; ?>
