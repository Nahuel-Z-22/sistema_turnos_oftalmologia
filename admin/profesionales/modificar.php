<?php
require_once "../includes/funciones.php";
requerir_login('../');
verificar_post();

$id = (int)$_POST['id_profesional'];
$servicios = array_map('intval', $_POST['servicios'] ?? []);

if (!$servicios) {
    redirigir("editar.php?id=$id", 'danger', 'Elegí al menos un servicio.');
}

try {
    $conexion->beginTransaction();

    $q = $conexion->prepare("UPDATE profesionales
                             SET nombre = :nombre, apellido = :apellido, dni = :dni, matricula = :matricula,
                                 telefono = :telefono, email = :email, id_especialidad = :id_especialidad, activo = :activo
                             WHERE id_profesional = :id");
    $q->execute([
        ':nombre' => trim($_POST['nombre']),
        ':apellido' => trim($_POST['apellido']),
        ':dni' => trim($_POST['dni']),
        ':matricula' => trim($_POST['matricula']),
        ':telefono' => valor_o_null($_POST['telefono'] ?? null),
        ':email' => valor_o_null($_POST['email'] ?? null),
        ':id_especialidad' => (int)$_POST['id_especialidad'],
        ':activo' => isset($_POST['activo']) ? 1 : 0,
        ':id' => $id,
    ]);

    // Se reemplazan los servicios del profesional
    $conexion->prepare("DELETE FROM profesional_servicio WHERE id_profesional = :id")->execute([':id' => $id]);
    $qs = $conexion->prepare("INSERT INTO profesional_servicio (id_profesional, id_servicio) VALUES (:p, :s)");
    foreach (array_unique($servicios) as $idServicio) {
        $qs->execute([':p' => $id, ':s' => $idServicio]);
    }

    $conexion->commit();
    redirigir('listar.php', 'success', 'Profesional actualizado.');
} catch (PDOException $e) {
    $conexion->rollBack();
    redirigir("editar.php?id=$id", 'danger', mensaje_error_bd($e));
}
