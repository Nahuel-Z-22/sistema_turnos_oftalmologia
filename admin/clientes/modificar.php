<?php
require_once "../includes/funciones.php";
requerir_login('../');
verificar_post();

$id = (int)$_POST['id_cliente'];
try {
    $q = $conexion->prepare("UPDATE clientes
                             SET nombre = :nombre, apellido = :apellido, dni = :dni, telefono = :telefono,
                                 email = :email, fecha_nacimiento = :nacimiento, activo = :activo
                             WHERE id_cliente = :id");
    $q->execute([
        ':nombre' => trim($_POST['nombre']),
        ':apellido' => trim($_POST['apellido']),
        ':dni' => trim($_POST['dni']),
        ':telefono' => valor_o_null($_POST['telefono'] ?? null),
        ':email' => valor_o_null($_POST['email'] ?? null),
        ':nacimiento' => valor_o_null($_POST['fecha_nacimiento'] ?? null),
        ':activo' => isset($_POST['activo']) ? 1 : 0,
        ':id' => $id,
    ]);
    redirigir('listar.php', 'success', 'Paciente actualizado.');
} catch (PDOException $e) {
    redirigir("editar.php?id=$id", 'danger', mensaje_error_bd($e));
}
