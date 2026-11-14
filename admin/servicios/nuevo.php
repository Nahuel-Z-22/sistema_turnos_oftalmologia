<?php
require_once "../includes/funciones.php";
requerir_login('../');

$s = [];

$raiz = '../';
$titulo = 'Nuevo servicio';
require "../includes/header.php";
?>
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white"><h4 class="mb-0">Nuevo servicio</h4></div>
            <div class="card-body">
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
