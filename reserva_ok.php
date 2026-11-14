<?php
// Reserva online · Paso 3: confirmación (Sofía + Belén). Pública.
require_once "admin/includes/funciones.php";
require_once "admin/includes/turnos_funciones.php";

$idTurno = (int)($_SESSION['reserva_ok'] ?? 0);
if (!$idTurno) {
    header('Location: index.php');
    exit;
}

$q = $conexion->prepare("
    SELECT t.id_turno, t.fecha, t.hora_inicio, c.nombre, c.apellido,
           p.apellido AS prof_apellido, p.nombre AS prof_nombre, esp.nombre AS especialidad
    FROM turnos t
    INNER JOIN clientes c ON c.id_cliente = t.id_cliente
    INNER JOIN profesionales p ON p.id_profesional = t.id_profesional
    INNER JOIN especialidades esp ON esp.id_especialidad = p.id_especialidad
    WHERE t.id_turno = :id");
$q->execute([':id' => $idTurno]);
$t = $q->fetch();
if (!$t) {
    unset($_SESSION['reserva_ok']);
    header('Location: index.php');
    exit;
}

$titulo = 'Turno reservado';
require "parciales/cabecera.php";
?>

    <section id="turnos">
        <h2>¡Listo, <?= e($t['nombre']) ?>!</h2>

        <div class="pasos">
            <span>1 · Día y horario</span>
            <span>2 · Tus datos</span>
            <span class="actual">3 · Listo</span>
        </div>

        <form>
            <div class="resumen">
                <strong>Turno N° <?= $t['id_turno'] ?></strong><br>
                Paciente: <?= e($t['apellido'] . ', ' . $t['nombre']) ?><br>
                Dr/a. <?= e($t['prof_apellido'] . ', ' . $t['prof_nombre']) ?> (<?= e($t['especialidad']) ?>)<br>
                <?= ucfirst(fecha_larga($t['fecha'])) ?> a las <?= substr($t['hora_inicio'], 0, 5) ?> hs
            </div>
            <p>Tu turno quedó <strong>pendiente de confirmación</strong>. Te vamos a contactar para confirmarlo.</p>
            <p class="ayuda">Si no podés asistir, avisanos al 3704-000000.</p>
            <a href="index.php" class="boton">Volver al inicio</a>
        </form>
    </section>

<?php require "parciales/pie.php"; ?>
