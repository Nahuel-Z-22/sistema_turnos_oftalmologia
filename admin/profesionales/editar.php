<?php
require_once "../includes/funciones.php";
requerir_login('../');

$q = $conexion->prepare("SELECT * FROM profesionales WHERE id_profesional = :id");
$q->execute([':id' => $_GET['id'] ?? 0]);
$p = $q->fetch();
if (!$p) redirigir('listar.php', 'danger', 'Profesional no encontrado.');

$especialidades = $conexion->query("SELECT * FROM especialidades ORDER BY nombre")->fetchAll();
$servicios = $conexion->query("SELECT * FROM servicios ORDER BY nombre")->fetchAll();

// Servicios actuales del profesional (para marcarlos como seleccionados)
$qs = $conexion->prepare("SELECT id_servicio FROM profesional_servicio WHERE id_profesional = :id");
$qs->execute([':id' => $p['id_profesional']]);
$serviciosDelProfesional = $qs->fetchAll(PDO::FETCH_COLUMN);

$raiz = '../';
$titulo = 'Editar profesional';
require "../includes/header.php";
?>
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card shadow-sm">
            <div class="card-header bg-warning"><h4 class="mb-0">Editar profesional</h4></div>
            <div class="card-body p-4">
                <form action="modificar.php" method="POST">
                    <input type="hidden" name="id_profesional" value="<?= $p['id_profesional'] ?>">
                    <?php require "_formulario.php"; ?>
                    <a class="btn btn-secondary" href="listar.php">Cancelar</a>
                    <button class="btn btn-warning">Guardar cambios</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require "../includes/footer.php"; ?>
