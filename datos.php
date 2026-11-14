<?php
// Reserva online · Paso 2: datos del paciente (Sofía + Belén). Pública.
require_once "admin/includes/funciones.php";
require_once "admin/includes/turnos_funciones.php";

$idProfesional = (int)($_GET['id_profesional'] ?? 0);
$fecha = $_GET['fecha'] ?? '';
$hora = $_GET['hora'] ?? '';
$volver = 'reservar.php?' . http_build_query(['id_profesional' => $idProfesional, 'fecha' => $fecha]);

// El profesional tiene que atender la Consulta y el horario tiene que seguir libre
$profesional = null;
foreach (profesionales_para_reserva($conexion) as $p) {
    if ($p['id_profesional'] == $idProfesional) {
        $profesional = $p;
    }
}
$consulta = servicios_del_profesional($conexion, $idProfesional, [SERVICIO_CONSULTA]);

if (!$profesional || !$consulta) {
    redirigir('reservar.php', 'danger', 'Elegí un profesional.');
}
if (!in_array($hora, horarios_libres($conexion, $idProfesional, $fecha, duracion_total($consulta)), true)) {
    redirigir($volver, 'danger', 'Ese horario ya no está disponible. Elegí otro.');
}

// Si hubo un error al guardar, se vuelven a mostrar los datos que había escrito
$d = $_SESSION['datos_reserva'] ?? [];
unset($_SESSION['datos_reserva']);

$titulo = 'Tus datos';
require "parciales/cabecera.php";
?>

    <section id="turnos">
        <h2>Solicitá tu turno</h2>

        <div class="pasos">
            <span>1 · Día y horario</span>
            <span class="actual">2 · Tus datos</span>
            <span>3 · Listo</span>
        </div>

        <form action="guardar_reserva.php" method="POST">
            <?= csrf_input() ?>
            <input type="hidden" name="id_profesional" value="<?= $idProfesional ?>">
            <input type="hidden" name="fecha" value="<?= e($fecha) ?>">
            <input type="hidden" name="hora" value="<?= e($hora) ?>">

            <div class="resumen">
                <strong><?= e($consulta[0]['nombre']) ?></strong><br>
                Dr/a. <?= e($profesional['apellido'] . ', ' . $profesional['nombre']) ?> (<?= e($profesional['especialidad']) ?>)<br>
                <?= ucfirst(fecha_larga($fecha)) ?> a las <?= e($hora) ?> hs<br>
                Valor de la consulta: <?= plata($consulta[0]['precio']) ?>
            </div>

            <input type="text" name="nombre" placeholder="Nombre" maxlength="100" value="<?= e($d['nombre'] ?? '') ?>" required>

            <input type="text" name="apellido" placeholder="Apellido" maxlength="100" value="<?= e($d['apellido'] ?? '') ?>" required>

            <input type="text" name="dni" placeholder="DNI (solo números)" inputmode="numeric" pattern="[0-9]{6,15}" title="Solo números, sin puntos" maxlength="15" value="<?= e($d['dni'] ?? '') ?>" required>

            <input type="email" name="email" placeholder="Correo electrónico" maxlength="150" value="<?= e($d['email'] ?? '') ?>">

            <input type="tel" name="telefono" placeholder="Teléfono" maxlength="30" value="<?= e($d['telefono'] ?? '') ?>" required>

            <button type="submit">Confirmar turno</button>
            <a href="<?= e($volver) ?>" class="boton-borde">Volver</a>
        </form>
    </section>

<?php require "parciales/pie.php"; ?>
