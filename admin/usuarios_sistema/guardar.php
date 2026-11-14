<?php
require_once "../includes/funciones.php";
requerir_admin('../');
verificar_post();

try {
    $q = $conexion->prepare("INSERT INTO usuarios_sistema (usuario, clave_hash, nombre_completo, email, id_rol, activo)
                             VALUES (:usuario, :clave, :nombre, :email, :id_rol, :activo)");
    $q->execute([
        ':usuario' => trim($_POST['usuario']),
        ':clave' => password_hash($_POST['clave'], PASSWORD_DEFAULT),   // nunca se guarda la clave en texto plano
        ':nombre' => trim($_POST['nombre_completo']),
        ':email' => valor_o_null($_POST['email'] ?? null),
        ':id_rol' => (int)$_POST['id_rol'],
        ':activo' => isset($_POST['activo']) ? 1 : 0,
    ]);
    redirigir('listar.php', 'success', 'Usuario creado.');
} catch (PDOException $e) {
    redirigir('nuevo.php', 'danger', mensaje_error_bd($e));
}
