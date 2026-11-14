<?php
// Devuelve en JSON los servicios que hace un profesional (Belén: obtener_servicios.php).
// Lo usa el formulario de turnos con fetch.
require_once "../includes/funciones.php";
require_once "../includes/turnos_funciones.php";

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}

$idProfesional = (int)($_GET['id_profesional'] ?? 0);
echo json_encode($idProfesional ? servicios_del_profesional($conexion, $idProfesional) : []);
