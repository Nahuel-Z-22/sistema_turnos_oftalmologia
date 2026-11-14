<?php
require_once "../includes/funciones.php";
requerir_admin('../');
verificar_post();

$id = (int)$_POST['id_usuario'];
$idRol = (int)$_POST['id_rol'];
$activo = isset($_POST['activo']) ? 1 : 0;

// Nombre del rol elegido (para saber si sigue siendo administrador)
$qr = $conexion->prepare("SELECT nombre FROM roles WHERE id_rol = :id");
$qr->execute([':id' => $idRol]);
$nombreRol = $qr->fetchColumn();
if (!$nombreRol) {
    redirigir("editar.php?id=$id", 'danger', 'El rol elegido no existe.');
}

// Un admin no puede quitarse a sí mismo el rol ni desactivarse (se quedaría afuera)
if ($id == $_SESSION['id_usuario'] && ($nombreRol !== ROL_ADMIN || !$activo)) {
    redirigir("editar.php?id=$id", 'danger', 'No podés quitarte el rol de administrador ni desactivarte a vos mismo.');
}

try {
    $sql = "UPDATE usuarios_sistema SET usuario = :usuario, nombre_completo = :nombre, email = :email,
                   id_rol = :id_rol, activo = :activo";
    $params = [
        ':usuario' => trim($_POST['usuario']),
        ':nombre' => trim($_POST['nombre_completo']),
        ':email' => valor_o_null($_POST['email'] ?? null),
        ':id_rol' => $idRol,
        ':activo' => $activo,
        ':id' => $id,
    ];
    // Solo se cambia la contraseña si se escribió una nueva
    if (($_POST['clave'] ?? '') !== '') {
        $sql .= ", clave_hash = :clave";
        $params[':clave'] = password_hash($_POST['clave'], PASSWORD_DEFAULT);
    }
    $sql .= " WHERE id_usuario = :id";

    $conexion->prepare($sql)->execute($params);

    if ($id == $_SESSION['id_usuario']) {
        $_SESSION['usuario'] = trim($_POST['usuario']);
        $_SESSION['nombre'] = trim($_POST['nombre_completo']);
    }
    redirigir('listar.php', 'success', 'Usuario actualizado.');
} catch (PDOException $e) {
    redirigir("editar.php?id=$id", 'danger', mensaje_error_bd($e));
}
