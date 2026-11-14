<?php
require_once "../includes/funciones.php";
requerir_login('../');

$q = $conexion->prepare("SELECT * FROM clientes WHERE id_cliente = :id");
$q->execute([':id' => $_GET['id'] ?? 0]);
$c = $q->fetch();
if (!$c) redirigir('listar.php', 'danger', 'Paciente no encontrado.');

$raiz = '../';
$titulo = 'Editar paciente';
require "../includes/header.php";
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-warning"><h4 class="mb-0">Editar paciente</h4></div>
            <div class="card-body p-4">
                <form action="modificar.php" method="POST">
                    <input type="hidden" name="id_cliente" value="<?= $c['id_cliente'] ?>">
                    <?php require "_formulario.php"; ?>
                    <a class="btn btn-secondary" href="listar.php">Cancelar</a>
                    <button class="btn btn-warning">Guardar cambios</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require "../includes/footer.php"; ?>
