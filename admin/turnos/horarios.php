<?php
// Devuelve en JSON la agenda del día de un profesional, libres y ocupados (Belén: obtener_horarios.php).
// Recibe por GET: id_profesional, fecha, servicios[] (para saber cuánto dura el turno) y excluir (turno que se edita)
require_once "../includes/funciones.php";
require_once "../includes/turnos_funciones.php";

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}

$idProfesional = (int)($_GET['id_profesional'] ?? 0);
$fecha = $_GET['fecha'] ?? '';

$servicios = servicios_del_profesional($conexion, $idProfesional, $_GET['servicios'] ?? []);
$duracion = duracion_total($servicios);

// Todos los horarios del día con su estado: [{"hora":"08:00","libre":true}, {"hora":"08:20","libre":false}, ...]
echo json_encode(agenda_del_dia($conexion, $idProfesional, $fecha, $duracion, (int)($_GET['excluir'] ?? 0)));
