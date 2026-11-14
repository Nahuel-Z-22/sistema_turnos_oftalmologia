<?php
// Funciones comunes: sesión, control de acceso, mensajes y seguridad.
// (Es el mismo archivo de crud: solo cambia el nombre del rol administrador
//  y se sacaron las funciones de libros.)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../conexion.php';

// Nombre del rol administrador en la tabla roles
const ROL_ADMIN = 'Administrador';

// Escapa texto para mostrarlo en HTML
function e($texto)
{
    return htmlspecialchars((string)($texto ?? ''), ENT_QUOTES, 'UTF-8');
}

// Exige que haya un usuario logueado. $raiz = ruta hasta la carpeta admin ('' o '../')
function requerir_login($raiz = '')
{
    if (!isset($_SESSION['usuario'])) {
        header('Location: ' . $raiz . 'index.php');
        exit;
    }
}

// Exige rol administrador
function requerir_admin($raiz = '')
{
    requerir_login($raiz);
    if (($_SESSION['rol'] ?? '') !== ROL_ADMIN) {
        flash('danger', 'Solo un administrador puede acceder a esa sección.');
        header('Location: ' . $raiz . 'home.php');
        exit;
    }
}

function es_admin()
{
    return ($_SESSION['rol'] ?? '') === ROL_ADMIN;
}

// Mensajes que se muestran una sola vez (después de guardar, eliminar, etc.)
function flash($tipo, $mensaje)
{
    $_SESSION['flash'][] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

function mostrar_flash()
{
    foreach ($_SESSION['flash'] ?? [] as $f) {
        echo '<div class="alert alert-' . e($f['tipo']) . ' alert-dismissible fade show">'
            . e($f['mensaje'])
            . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
    unset($_SESSION['flash']);
}

// Token CSRF: evita que otra página envíe formularios en nombre del usuario
function csrf_token()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_input()
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

// Verifica que sea POST y que el token sea válido
function verificar_post()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals(csrf_token(), $_POST['csrf'] ?? '')) {
        http_response_code(400);
        die('Solicitud no válida.');
    }
}

// Botón "Eliminar" que envía por POST (más seguro que un link GET)
function boton_eliminar($accion, $id, $pregunta = '¿Eliminar registro?')
{
    return '<form action="' . e($accion) . '" method="POST" class="d-inline" onsubmit="return confirm(\'' . e($pregunta) . '\')">'
        . csrf_input()
        . '<input type="hidden" name="id" value="' . (int)$id . '">'
        . '<button class="btn btn-danger btn-sm">Eliminar</button></form>';
}

// Traduce errores de MySQL a mensajes entendibles
function mensaje_error_bd(PDOException $e)
{
    $codigo = $e->errorInfo[1] ?? 0;
    switch ($codigo) {
        case 1062:
            return 'Ya existe un registro con ese dato (valor duplicado).';
        case 1451:
            return 'No se puede eliminar: el registro está siendo usado en otra tabla. Podés desactivarlo desde Editar.';
        case 1452:
            return 'El dato relacionado seleccionado no existe.';
        default:
            return 'Error de base de datos: ' . $e->getMessage();
    }
}

// Devuelve null si el campo viene vacío
function valor_o_null($valor)
{
    $valor = trim((string)($valor ?? ''));
    return $valor === '' ? null : $valor;
}

// Formatea un importe: 25000 -> $ 25.000
function plata($importe)
{
    return '$ ' . number_format((float)$importe, 0, ',', '.');
}

// Redirige con un mensaje
function redirigir($url, $tipo = null, $mensaje = null)
{
    if ($tipo) {
        flash($tipo, $mensaje);
    }
    header('Location: ' . $url);
    exit;
}
