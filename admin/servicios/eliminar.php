<?php
require_once "../includes/funciones.php";
requerir_login('../');
verificar_post();

try {
    // Si el servicio ya se usó en algún turno, la BD no deja borrarlo (error 1451)
    $q = $conexion->prepare("DELETE FROM servicios WHERE id_servicio = :id");
    $q->execute([':id' => (int)$_POST['id']]);
    redirigir('listar.php', 'success', 'Servicio eliminado.');
} catch (PDOException $e) {
    redirigir('listar.php', 'danger', mensaje_error_bd($e));
}
