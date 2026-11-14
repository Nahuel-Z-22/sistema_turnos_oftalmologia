<?php
// Principio de cada página pública (diseño de Sofía).
// Antes de incluirlo definir $titulo (opcional). La página ya tiene que haber incluido admin/includes/funciones.php
$titulo = $titulo ?? 'Centro Oftalmológico';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OCULUS - <?= e($titulo) ?></title>
    <!-- estilos.css = diseño de Sofía (original). reserva.css = lo agregado para la reserva online.
         El ?v= cambia cada vez que se modifica el archivo, así el navegador no usa una copia vieja. -->
    <link rel="stylesheet" href="assets/estilos.css?v=<?= @filemtime(__DIR__ . '/../assets/estilos.css') ?>">
    <link rel="stylesheet" href="assets/reserva.css?v=<?= @filemtime(__DIR__ . '/../assets/reserva.css') ?>">
</head>

<body>

    <!-- ENCABEZADO -->
    <header>
        <h2><a href="index.php" class="logo">OCULUS</a></h2>

        <nav>
            <a href="index.php#inicio">Inicio</a>
            <a href="index.php#servicios">Servicios</a>
            <a href="index.php#turnos">Turnos</a>
            <a href="index.php#contacto">Contacto</a>
            <a href="admin/" class="acceso-personal">Acceso personal</a>
        </nav>
    </header>

    <?php
    // Mensajes de error o confirmación (los mismos de flash() del sistema)
    if (!empty($_SESSION['flash'])) {
        echo '<div style="padding: 20px 20px 0;">';
        foreach ($_SESSION['flash'] as $f) {
            echo '<div class="aviso aviso-' . e($f['tipo']) . '">' . e($f['mensaje']) . '</div>';
        }
        echo '</div>';
        unset($_SESSION['flash']);
    }
    ?>
