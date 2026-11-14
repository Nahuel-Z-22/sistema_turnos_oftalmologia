<?php
require_once "../includes/funciones.php";
requerir_login('../');
verificar_post();

try {
    $q = $conexion->prepare("INSERT INTO clientes (nombre, apellido, dni, telefono, email, fecha_nacimiento, activo)
                             VALUES (:nombre, :apellido, :dni, :telefono, :email, :nacimiento, :activo)");
    $q->execute([
        ':nombre' => trim($_POST['nombre']),
        ':apellido' => trim($_POST['apellido']),
        ':dni' => trim($_POST['dni']),
        ':telefono' => valor_o_null($_POST['telefono'] ?? null),
        ':email' => valor_o_null($_POST['email'] ?? null),
        ':nacimiento' => valor_o_null($_POST['fecha_nacimiento'] ?? null),
        ':activo' => isset($_POST['activo']) ? 1 : 0,
    ]);
    redirigir('listar.php', 'success', 'Paciente guardado.');
} catch (PDOException $e) {
    // 1062 = DNI repetido
    redirigir('nuevo.php', 'danger', mensaje_error_bd($e));
}
