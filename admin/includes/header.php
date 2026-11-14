<?php
// Antes de incluir este archivo definir:
//   $raiz   = '' (si la página está en la carpeta admin) o '../' (si está en un módulo)
//   $titulo = título de la página
$raiz = $raiz ?? '';
$titulo = $titulo ?? 'Oculus';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e($titulo) ?> | Oculus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400..700&family=Nunito+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Colores del sistema (diseño de Belén). La web pública usa assets/estilos.css (Sofía). -->
    <link href="<?= $raiz ?>../assets/admin.css?v=<?= @filemtime(__DIR__ . '/../../assets/admin.css') ?>" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark navbar-clinica">
    <div class="container">
        <a class="navbar-brand" href="<?= $raiz ?>home.php">Clínica Oculus</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="<?= $raiz ?>turnos/listar.php">Turnos</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $raiz ?>clientes/listar.php">Pacientes</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $raiz ?>profesionales/listar.php">Profesionales</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $raiz ?>servicios/listar.php">Servicios</a></li>
                <?php if (es_admin()) { ?>
                    <li class="nav-item"><a class="nav-link" href="<?= $raiz ?>usuarios_sistema/listar.php">Usuarios del sistema</a></li>
                <?php } ?>
            </ul>
            <a class="nav-link text-white-50 me-3" href="<?= $raiz ?>../" target="_blank">Ver web</a>
            <span class="navbar-text text-white me-3">👤 <?= e($_SESSION['nombre'] ?? $_SESSION['usuario']) ?> (<?= e($_SESSION['rol']) ?>)</span>
            <a class="btn btn-outline-light btn-sm" href="<?= $raiz ?>logout.php">Salir</a>
        </div>
    </div>
</nav>
<main class="container py-4">
<?php mostrar_flash(); ?>
