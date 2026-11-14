<?php
// Valida el login (Belén). Lo llama login.js con fetch y responde en JSON.
require_once "includes/funciones.php";   // inicia la sesión y trae la conexión

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Método no permitido'
    ]);
    exit;
}

$usuario = trim($_POST['usuario'] ?? '');
$clave = $_POST['clave'] ?? '';

// INNER JOIN con roles: en la sesión se guarda el NOMBRE del rol (lo usan header.php y es_admin())
$sql = "SELECT u.id_usuario, u.usuario, u.clave_hash, u.nombre_completo, u.activo, r.nombre AS rol
        FROM usuarios_sistema u
        INNER JOIN roles r ON r.id_rol = u.id_rol
        WHERE u.usuario = :usuario
        LIMIT 1";

$consulta = $conexion->prepare($sql);
$consulta->execute(['usuario' => $usuario]);

$datos = $consulta->fetch(PDO::FETCH_ASSOC);

// Pueden entrar todos los roles activos (Administrador, Recepción, Profesional).
// Las secciones solo para administradores se controlan con requerir_admin().
if (
    $datos &&
    password_verify($clave, $datos['clave_hash']) &&
    $datos['activo'] == 1
) {
    session_regenerate_id(true);

    $_SESSION['id_usuario'] = (int)$datos['id_usuario'];
    $_SESSION['usuario'] = $datos['usuario'];
    $_SESSION['nombre'] = $datos['nombre_completo'];
    $_SESSION['rol'] = $datos['rol'];

    // Se registra el último acceso
    $conexion->prepare("UPDATE usuarios_sistema SET ultimo_acceso = NOW() WHERE id_usuario = :id")
             ->execute(['id' => $datos['id_usuario']]);

    echo json_encode(['ok' => true]);

} else {
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Usuario o contraseña incorrectos'
    ]);
}
