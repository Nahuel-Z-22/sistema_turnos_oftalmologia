<?php
// Reserva online · Paso 1: profesional, día y horario (Sofía + Belén). Pública.
require_once "admin/includes/funciones.php";
require_once "admin/includes/turnos_funciones.php";

$profesionales = profesionales_para_reserva($conexion);

$idProfesional = (int)($_GET['id_profesional'] ?? 0);
$fecha = $_GET['fecha'] ?? '';

// Datos del profesional elegido (tiene que estar en la lista de los que se pueden reservar)
$profesional = null;
foreach ($profesionales as $p) {
    if ($p['id_profesional'] == $idProfesional) {
        $profesional = $p;
    }
}

// Agenda del día para una Consulta oftalmológica con ese profesional:
// todos los horarios, los libres se pueden elegir y los ocupados se muestran deshabilitados
$horarios = [];
$hayLibres = false;
$error = '';
if ($profesional && $fecha !== '') {
    if (!fecha_valida($fecha) || $fecha < date('Y-m-d')) {
        $error = 'Elegí una fecha de hoy en adelante.';
    } elseif (!in_array((int)date('N', strtotime($fecha)), DIAS_ATENCION)) {
        $error = 'Ese día no atendemos (lunes a viernes de 8 a 18 hs). Probá con otra fecha.';
    } else {
        $consulta = servicios_del_profesional($conexion, $idProfesional, [SERVICIO_CONSULTA]);
        $horarios = agenda_del_dia($conexion, $idProfesional, $fecha, duracion_total($consulta));
        $hayLibres = in_array(true, array_column($horarios, 'libre'), true);
        if (!$horarios) {
            $error = 'Ya no quedan horarios para hoy. Probá con otra fecha.';
        } elseif (!$hayLibres) {
            $error = 'Todos los horarios de ese día están ocupados. Probá con otra fecha.';
        }
    }
}

$titulo = 'Reservar turno';
require "parciales/cabecera.php";
?>

    <section id="turnos">
        <h2>Solicitá tu turno</h2>

        <div class="pasos">
            <span class="actual">1 · Día y horario</span>
            <span>2 · Tus datos</span>
            <span>3 · Listo</span>
        </div>

        <!-- Elegir profesional y día -->
        <form action="reservar.php" method="GET">
            <label for="id_profesional">Profesional</label>
            <select name="id_profesional" id="id_profesional" required>
                <option value="">Elegí un profesional</option>
                <?php foreach ($profesionales as $p) { ?>
                    <option value="<?= $p['id_profesional'] ?>" <?= $p['id_profesional'] == $idProfesional ? 'selected' : '' ?>>
                        <?= e('Dr/a. ' . $p['apellido'] . ', ' . $p['nombre'] . ' (' . $p['especialidad'] . ')') ?>
                    </option>
                <?php } ?>
            </select>

            <label for="fecha">Día</label>
            <input type="date" name="fecha" id="fecha" min="<?= date('Y-m-d') ?>" value="<?= e($fecha) ?>" required>
            <p class="ayuda">Atendemos de lunes a viernes de 8 a 18 hs.</p>

            <button type="submit">Ver horarios</button>
        </form>

        <?php if ($error) { ?>
            <div class="aviso aviso-danger" style="margin-top: 25px;"><?= e($error) ?></div>
        <?php } ?>

        <?php if ($horarios) { ?>
            <!-- Elegir horario y pasar a los datos del paciente -->
            <form action="datos.php" method="GET" style="margin-top: 25px;">
                <input type="hidden" name="id_profesional" value="<?= $idProfesional ?>">
                <input type="hidden" name="fecha" value="<?= e($fecha) ?>">

                <p><strong>Horarios</strong> del <?= fecha_larga($fecha) ?></p>
                <div class="horarios">
                    <?php foreach ($horarios as $i => $h) { ?>
                        <?php if ($h['libre']) { ?>
                            <input type="radio" name="hora" id="h<?= $i ?>" value="<?= $h['hora'] ?>" required>
                            <label for="h<?= $i ?>"><?= $h['hora'] ?></label>
                        <?php } else { ?>
                            <!-- Ocupado: ya hay un turno en ese horario, no se puede elegir -->
                            <input type="radio" id="h<?= $i ?>" disabled>
                            <label for="h<?= $i ?>" class="ocupado" title="Horario ocupado"><?= $h['hora'] ?></label>
                        <?php } ?>
                    <?php } ?>
                </div>
                <p class="referencias">
                    <span class="ref-libre"></span> Libre
                    <span class="ref-ocupado"></span> Ocupado
                </p>

                <?php if ($hayLibres) { ?>
                    <button type="submit">Continuar</button>
                <?php } ?>
            </form>
        <?php } ?>
    </section>

<?php require "parciales/pie.php"; ?>
