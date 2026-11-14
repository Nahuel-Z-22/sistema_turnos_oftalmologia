<?php
// Funciones de turnos (Belén). Las usa el módulo admin/turnos/ y la reserva online de la web pública.
// Antes de incluir este archivo tiene que estar incluido funciones.php (trae $conexion).

// Horario de atención (el mismo que figura en la web: lunes a viernes de 8 a 18 hs)
const FRANJAS_ATENCION = [['08:00', '12:00'], ['14:00', '18:00']];
const DIAS_ATENCION = [1, 2, 3, 4, 5];   // 1 = lunes ... 7 = domingo

// Cada cuántos minutos puede empezar un turno
const MINUTOS_ENTRE_TURNOS = 20;

// Estados de la tabla estados_turno
const ESTADO_PENDIENTE = 1;
const ESTADO_CONFIRMADO = 2;
const ESTADO_ATENDIDO = 3;
const ESTADO_CANCELADO = 4;

// Color de Bootstrap para cada estado (el texto siempre se muestra al lado)
const COLOR_ESTADO = [1 => 'warning', 2 => 'primary', 3 => 'success', 4 => 'secondary'];

// Servicio que se reserva desde la web (Consulta oftalmológica)
const SERVICIO_CONSULTA = 1;

// Servicios activos que hace un profesional.
// Si se pasa $ids, devuelve solo esos (sirve para validar lo que llega del formulario).
function servicios_del_profesional($conexion, $idProfesional, $ids = null)
{
    $q = $conexion->prepare("SELECT s.id_servicio, s.nombre, s.precio, s.duracion_min
                             FROM servicios s
                             INNER JOIN profesional_servicio ps ON ps.id_servicio = s.id_servicio
                             WHERE ps.id_profesional = :p AND s.activo = 1
                             ORDER BY s.nombre");
    $q->execute([':p' => (int)$idProfesional]);
    $servicios = $q->fetchAll();

    if ($ids !== null) {
        $ids = array_map('intval', (array)$ids);
        $servicios = array_values(array_filter($servicios, function ($s) use ($ids) {
            return in_array((int)$s['id_servicio'], $ids);
        }));
    }
    return $servicios;
}

// Suma la duración (en minutos) de una lista de servicios
function duracion_total($servicios)
{
    $minutos = 0;
    foreach ($servicios as $s) {
        $minutos += (int)$s['duracion_min'];
    }
    return $minutos;
}

// Suma minutos a una hora: ('09:40', 30) -> '10:10'
function sumar_minutos($hora, $minutos)
{
    return date('H:i', strtotime($hora) + $minutos * 60);
}

// true si la fecha tiene formato AAAA-MM-DD válido
function fecha_valida($fecha)
{
    $d = DateTime::createFromFormat('Y-m-d', (string)$fecha);
    return $d && $d->format('Y-m-d') === $fecha;
}

// Agenda del día de un profesional: TODOS los horarios, cada uno marcado como libre u ocupado.
//   Devuelve [['hora' => '08:00', 'libre' => true], ['hora' => '08:20', 'libre' => false], ...]
//   $duracion       = minutos que dura el turno (suma de los servicios)
//   $excluirTurno   = id de un turno que no se cuenta como ocupado (al editar ese mismo turno)
// Un horario está libre si el turno entero (inicio + duración) no se pisa con otro turno
// no cancelado del mismo profesional y termina dentro del horario de atención.
// Si la fecha es hoy, los horarios que ya pasaron no se muestran.
function agenda_del_dia($conexion, $idProfesional, $fecha, $duracion = MINUTOS_ENTRE_TURNOS, $excluirTurno = null)
{
    if (!fecha_valida($fecha) || !in_array((int)date('N', strtotime($fecha)), DIAS_ATENCION)) {
        return [];   // fecha inválida o día sin atención (fin de semana)
    }
    $duracion = max((int)$duracion, MINUTOS_ENTRE_TURNOS);

    // Turnos ya tomados ese día (los cancelados liberan el horario)
    $q = $conexion->prepare("SELECT hora_inicio, hora_fin FROM turnos
                             WHERE id_profesional = :p AND fecha = :f AND id_estado <> :cancelado
                               AND id_turno <> :excluir");
    $q->execute([
        ':p' => (int)$idProfesional,
        ':f' => $fecha,
        ':cancelado' => ESTADO_CANCELADO,
        ':excluir' => (int)$excluirTurno,
    ]);
    $ocupados = $q->fetchAll();

    // Si es hoy, no se ofrecen horarios que ya pasaron
    $ahora = ($fecha === date('Y-m-d')) ? date('H:i') : '';

    $agenda = [];
    foreach (FRANJAS_ATENCION as [$desde, $hasta]) {
        for ($inicio = $desde; sumar_minutos($inicio, $duracion) <= $hasta; $inicio = sumar_minutos($inicio, MINUTOS_ENTRE_TURNOS)) {
            $fin = sumar_minutos($inicio, $duracion);
            if ($inicio <= $ahora) {
                continue;
            }
            $pisado = false;
            foreach ($ocupados as $o) {
                // Dos turnos se pisan si uno empieza antes de que termine el otro
                if ($inicio < substr($o['hora_fin'], 0, 5) && substr($o['hora_inicio'], 0, 5) < $fin) {
                    $pisado = true;
                    break;
                }
            }
            $agenda[] = ['hora' => $inicio, 'libre' => !$pisado];
        }
    }
    return $agenda;
}

// Solo los horarios libres: ['08:00', '08:40', ...]. Es lo que se controla al guardar un turno.
function horarios_libres($conexion, $idProfesional, $fecha, $duracion = MINUTOS_ENTRE_TURNOS, $excluirTurno = null)
{
    $libres = [];
    foreach (agenda_del_dia($conexion, $idProfesional, $fecha, $duracion, $excluirTurno) as $h) {
        if ($h['libre']) {
            $libres[] = $h['hora'];
        }
    }
    return $libres;
}

// Bloquea la agenda del profesional hasta el commit/rollBack de la transacción.
// Si dos personas guardan un turno con el mismo profesional al mismo tiempo, la segunda espera
// a que termine la primera y recién ahí controla si el horario sigue libre (así no quedan turnos pisados).
// Se llama DENTRO de la transacción, justo antes de horarios_libres().
function bloquear_agenda($conexion, $idProfesional)
{
    $q = $conexion->prepare("SELECT id_profesional FROM profesionales WHERE id_profesional = :p FOR UPDATE");
    $q->execute([':p' => (int)$idProfesional]);
}

// Profesionales activos que atienden la Consulta (los que se pueden reservar desde la web)
function profesionales_para_reserva($conexion)
{
    $q = $conexion->prepare("SELECT p.id_profesional, p.apellido, p.nombre, e.nombre AS especialidad
                             FROM profesionales p
                             INNER JOIN especialidades e ON e.id_especialidad = p.id_especialidad
                             INNER JOIN profesional_servicio ps ON ps.id_profesional = p.id_profesional
                             WHERE p.activo = 1 AND ps.id_servicio = :consulta
                             ORDER BY e.nombre, p.apellido, p.nombre");
    $q->execute([':consulta' => SERVICIO_CONSULTA]);
    return $q->fetchAll();
}

// Badge con el nombre del estado (para el admin, que usa Bootstrap)
function badge_estado($idEstado, $nombre)
{
    return '<span class="badge text-bg-' . (COLOR_ESTADO[$idEstado] ?? 'light') . '">' . e($nombre) . '</span>';
}

// Fecha legible: 2026-11-17 -> martes 17/11/2026
function fecha_larga($fecha)
{
    $dias = [1 => 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'];
    return $dias[(int)date('N', strtotime($fecha))] . ' ' . date('d/m/Y', strtotime($fecha));
}
