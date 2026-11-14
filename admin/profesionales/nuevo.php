<?php
require_once "../includes/funciones.php";
requerir_login('../');

$p = [];
$especialidades = $conexion->query("SELECT * FROM especialidades ORDER BY nombre")->fetchAll();
$servicios = $conexion->query("SELECT * FROM servicios WHERE activo = 1 ORDER BY nombre")->fetchAll();
$serviciosDelProfesional = [];

$raiz = '../';
$titulo = 'Nuevo profesional';
require "../includes/header.php";
?>
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white"><h4 class="mb-0">Nuevo profesional</h4></div>
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
