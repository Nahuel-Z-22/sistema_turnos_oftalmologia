<?php
require_once "../includes/funciones.php";
requerir_login('../');
verificar_post();

$id = (int)$_POST['id_servicio'];
try {
    // Cambiar el precio no afecta a los turnos ya sacados: cada detalle guarda su precio_unitario
    $q = $conexion->prepare("UPDATE servicios
                             SET nombre = :nombre, descripcion = :descripcion, precio = :precio,
                                 duracion_min = :duracion, activo = :activo
                             WHERE id_servicio = :id");
    $q->execute([
        ':nombre' => trim($_POST['nombre']),
        ':descripcion' => valor_o_null($_POST['descripcion'] ?? null),
        ':precio' => (float)$_POST['precio'],
        ':duracion' => (int)$_POST['duracion_min'],
        ':activo' => isset($_POST['activo']) ? 1 : 0,
        ':id' => $id,
    ]);
    redirigir('listar.php', 'success', 'Servicio actualizado.');
} catch (PDOException $e) {
    redirigir("editar.php?id=$id", 'danger', mensaje_error_bd($e));
}
