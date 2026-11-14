<?php
require_once "../includes/funciones.php";
requerir_login('../');
verificar_post();

try {
    // La clave foránea no deja borrar un paciente que tiene turnos registrados:
    // en ese caso se lo desactiva desde Editar (baja lógica)
    $q = $conexion->prepare("DELETE FROM clientes WHERE id_cliente = :id");
    $q->execute([':id' => (int)$_POST['id']]);
    redirigir('listar.php', 'success', 'Paciente eliminado.');
} catch (PDOException $e) {
    redirigir('listar.php', 'danger', mensaje_error_bd($e));
}
