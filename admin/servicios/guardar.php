<?php
require_once "../includes/funciones.php";
requerir_login('../');
verificar_post();

try {
    $q = $conexion->prepare("INSERT INTO servicios (nombre, descripcion, precio, duracion_min, activo)
                             VALUES (:nombre, :descripcion, :precio, :duracion, :activo)");
    $q->execute([
        ':nombre' => trim($_POST['nombre']),
        ':descripcion' => valor_o_null($_POST['descripcion'] ?? null),
        ':precio' => (float)$_POST['precio'],
        ':duracion' => (int)$_POST['duracion_min'],
        ':activo' => isset($_POST['activo']) ? 1 : 0,
    ]);
    redirigir('listar.php', 'success', 'Servicio guardado.');
} catch (PDOException $e) {
    redirigir('nuevo.php', 'danger', mensaje_error_bd($e));
}
