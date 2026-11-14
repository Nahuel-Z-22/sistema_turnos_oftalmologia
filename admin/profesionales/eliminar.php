<?php
require_once "../includes/funciones.php";
requerir_login('../');
verificar_post();

$id = (int)$_POST['id'];
try {
    $conexion->beginTransaction();
    // Primero se borran sus servicios y después el profesional.
    // Si tiene turnos, la BD no deja borrarlo (clave foránea) y se deshace todo.
    $conexion->prepare("DELETE FROM profesional_servicio WHERE id_profesional = :id")->execute([':id' => $id]);
    $conexion->prepare("DELETE FROM profesionales WHERE id_profesional = :id")->execute([':id' => $id]);
    $conexion->commit();
    redirigir('listar.php', 'success', 'Profesional eliminado.');
} catch (PDOException $e) {
    $conexion->rollBack();
    redirigir('listar.php', 'danger', mensaje_error_bd($e));
}
