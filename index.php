<?php
// Landing page (Sofía). Es pública: no lleva requerir_login().
require_once "admin/includes/funciones.php";
require_once "admin/includes/turnos_funciones.php";

// Para el formulario de turnos: profesionales que se pueden reservar desde la web
$profesionales = profesionales_para_reserva($conexion);

// Para "Nuestros servicios": los servicios activos que se cargan desde el ABM de Servicios
$servicios = $conexion->query("SELECT nombre, descripcion, precio, duracion_min
                               FROM servicios
                               WHERE activo = 1
                               ORDER BY id_servicio")->fetchAll();

// Ícono de cada tarjeta según el nombre del servicio (si no coincide ninguno, va el ojo)
function icono_servicio($nombre)
{
    $iconos = [
        'consulta' => '🩺',
        'lente' => '👓',
        'tomograf' => '🔬',
        'campo' => '🎯',
        'fondo' => '🔦',
        'topograf' => '🗺️',
    ];
    foreach ($iconos as $palabra => $icono) {
        if (stripos($nombre, $palabra) !== false) {
            return $icono;
        }
    }
    return '👁️';
}

$titulo = 'Centro Oftalmológico';
require "parciales/cabecera.php";
?>

    <!-- INICIO -->

<section id="inicio" class="inicio">
    <div class="texto-inicio">
        <h1>Cuidamos tu visión</h1>

        <p>
            En OCULUS nos preocupamos por la salud
            de tus ojos.
        </p>

        <a href="#turnos" class="boton">
            Solicitar turno
        </a>
    </div>

    <div class="imagen-inicio">
        <img src="assets/img/consultorio.jpg" alt="Consultorio oftalmológico">

    </div>
</section>

    <!-- SERVICIOS -->

<section id="servicios">

    <h2>Nuestros servicios</h2>
    <p class="subtitulo">Cuidamos tus ojos en cada etapa de tu vida.</p>

    <div class="contenedor">

        <!-- Una tarjeta (diseño de Sofía) por cada servicio activo de la base -->
        <?php foreach ($servicios as $s) { ?>
            <div class="tarjeta">
                <div class="icono"><?= icono_servicio($s['nombre']) ?></div>
                <h3><?= e($s['nombre']) ?></h3>
                <p><?= e($s['descripcion'] ?: 'Consultá por este servicio.') ?></p>
                <p class="precio-servicio"><?= plata($s['precio']) ?> · <?= (int)$s['duracion_min'] ?> min</p>
            </div>
        <?php } ?>

        <?php if (!$servicios) { ?>
            <p>Pronto vas a ver acá nuestros servicios.</p>
        <?php } ?>

    </div>

</section>


    <!-- TURNOS -->
    <!-- El formulario ahora es el primer paso de la reserva online: elegís profesional y día,
         y en reservar.php se muestran los horarios libres. -->
    <section id="turnos">
        <h2>Solicitá tu turno</h2>
        <p class="subtitulo">Elegí el profesional y el día. Después te mostramos los horarios libres.</p>

        <form action="reservar.php" method="GET">
            <select name="id_profesional" required>
                <option value="">Profesional</option>
                <?php foreach ($profesionales as $p) { ?>
                    <option value="<?= $p['id_profesional'] ?>">
                        <?= e('Dr/a. ' . $p['apellido'] . ', ' . $p['nombre'] . ' (' . $p['especialidad'] . ')') ?>
                    </option>
                <?php } ?>
            </select>

            <input type="date" name="fecha" min="<?= date('Y-m-d') ?>" required>

            <button type="submit">Solicitar turno</button>
        </form>
    </section>

    <!-- CONTACTO -->
    <section id="contacto">
        <h2>Contacto</h2>
        <p>Estamos para ayudarte a cuidar tu visión.</p>
        <p>Teléfono: 3704-000000</p>
        <p>Horario: Lunes a viernes de 8 a 18 hs.</p>
    </section>

<?php require "parciales/pie.php"; ?>
