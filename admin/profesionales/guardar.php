<?php
require_once "../includes/funciones.php";
requerir_login('../');
verificar_post();

$servicios = array_map('intval', $_POST['servicios'] ?? []);
if (!$servicios) {
    redirigir('nuevo.php', 'danger', 'Elegí al menos un servicio.');
}

try {
    // Transacción: se guarda el profesional y sus servicios, o no se guarda nada
    $conexion->beginTransaction();

    $q = $conexion->prepare("INSERT INTO profesionales (nombre, apellido, dni, matricula, telefono, email, id_especialidad, activo)
                             VALUES (:nombre, :apellido, :dni, :matricula, :telefono, :email, :id_especialidad, :activo)");
    $q->execute([
        ':nombre' => trim($_POST['nombre']),
        ':apellido' => trim($_POST['apellido']),
        ':dni' => trim($_POST['dni']),
        ':matricula' => trim($_POST['matricula']),
        ':telefono' => valor_o_null($_POST['telefono'] ?? null),
        ':email' => valor_o_null($_POST['email'] ?? null),
        ':id_especialidad' => (int)$_POST['id_especialidad'],
        ':activo' => isset($_POST['activo']) ? 1 : 0,
    ]);
    $idProfesional = $conexion->lastInsertId();

    // Tabla puente profesional_servicio (relación muchos a muchos)
    $qs = $conexion->prepare("INSERT INTO profesional_servicio (id_profesional, id_servicio) VALUES (:p, :s)");
    foreach (array_unique($servicios) as $idServicio) {
        $qs->execute([':p' => $idProfesional, ':s' => $idServicio]);
    }

    $conexion->commit();
    redirigir('listar.php', 'success', 'Profesional guardado.');
} catch (PDOException $e) {
    $conexion->rollBack();
    redirigir('nuevo.php', 'danger', mensaje_error_bd($e));
}
