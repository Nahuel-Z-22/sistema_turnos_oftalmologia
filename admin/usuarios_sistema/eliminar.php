<?php
require_once "../includes/funciones.php";
requerir_admin('../');
verificar_post();

$id = (int)$_POST['id'];
if ($id == $_SESSION['id_usuario']) {
    redirigir('listar.php', 'danger', 'No podés eliminar tu propio usuario.');
}

try {
    // Si el usuario cargó turnos, la BD no deja borrarlo: se lo desactiva desde Editar
    $q = $conexion->prepare("DELETE FROM usuarios_sistema WHERE id_usuario = :id");
    $q->execute([':id' => $id]);
    redirigir('listar.php', 'success', 'Usuario eliminado.');
} catch (PDOException $e) {
    redirigir('listar.php', 'danger', mensaje_error_bd($e));
}
