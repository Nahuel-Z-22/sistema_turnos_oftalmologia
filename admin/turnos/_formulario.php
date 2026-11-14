<?php
// Formulario de turno (diseño de Belén: datos a la izquierda, resumen con el total a la derecha).
// Se usa en nuevo.php y editar.php. Variables esperadas:
//   $t                  datos del turno (vacío en nuevo.php)
//   $clientes, $profesionales
//   $serviciosDelTurno  array de id_servicio ya elegidos
//   $preciosGuardados   [id_servicio => precio] con que se sacó el turno (solo en editar.php)
//   $estados            lista de estados (solo en editar.php; en nuevo.php es null y arranca Pendiente)
if (!isset($clientes, $profesionales, $serviciosDelTurno)) exit; // no se abre directamente

$idTurno = (int)($t['id_turno'] ?? 0);
$fechaMinima = min(date('Y-m-d'), $t['fecha'] ?? date('Y-m-d'));
?>
<form id="formTurno" action="<?= $idTurno ? 'modificar.php' : 'guardar.php' ?>" method="POST">
    <?= csrf_input() ?>
    <?php if ($idTurno) { ?>
        <input type="hidden" name="id_turno" value="<?= $idTurno ?>">
    <?php } ?>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h5 class="mb-3">Datos del turno</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="id_cliente">Paciente</label>
                            <select class="form-select" name="id_cliente" id="id_cliente" required>
                                <option value="">Seleccionar paciente</option>
                                <?php foreach ($clientes as $c) { ?>
                                    <option value="<?= $c['id_cliente'] ?>" <?= ($t['id_cliente'] ?? null) == $c['id_cliente'] ? 'selected' : '' ?>>
                                        <?= e($c['apellido'] . ', ' . $c['nombre'] . ' (DNI ' . $c['dni'] . ')') ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <div class="form-text"><a href="../clientes/nuevo.php">+ Cargar paciente nuevo</a></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="id_profesional">Profesional</label>
                            <select class="form-select" name="id_profesional" id="id_profesional" required>
                                <option value="">Seleccionar profesional</option>
                                <?php foreach ($profesionales as $p) { ?>
                                    <option value="<?= $p['id_profesional'] ?>" <?= ($t['id_profesional'] ?? null) == $p['id_profesional'] ? 'selected' : '' ?>>
                                        <?= e($p['apellido'] . ', ' . $p['nombre'] . ' - ' . $p['especialidad']) ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <h5 class="mb-3 mt-3">Servicios del turno</h5>
                    <div id="listaServicios" class="mb-3">
                        <p class="text-muted">Primero seleccioná un profesional.</p>
                    </div>

                    <h5 class="mb-3 mt-4">Fecha y horario</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="fecha">Fecha</label>
                            <input type="date" class="form-control" name="fecha" id="fecha" min="<?= $fechaMinima ?>" value="<?= e($t['fecha'] ?? '') ?>" required>
                            <div class="form-text">Se atiende de lunes a viernes.</div>
                        </div>
                        <?php if ($estados !== null) { ?>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="id_estado">Estado</label>
                                <select class="form-select" name="id_estado" id="id_estado" required>
                                    <?php foreach ($estados as $est) { ?>
                                        <option value="<?= $est['id_estado'] ?>" <?= ($t['id_estado'] ?? 1) == $est['id_estado'] ? 'selected' : '' ?>><?= e($est['nombre']) ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Horarios disponibles</label>
                        <div id="listaHorarios"><p class="text-muted">Seleccioná un profesional, los servicios y una fecha.</p></div>
                        <input type="hidden" name="hora_inicio" id="hora_inicio" value="<?= e(substr($t['hora_inicio'] ?? '', 0, 5)) ?>">
                    </div>

                    <h5 class="mb-3 mt-4">Información adicional</h5>
                    <div class="mb-4">
                        <label class="form-label" for="observaciones">Observaciones</label>
                        <textarea class="form-control" name="observaciones" id="observaciones" rows="3" maxlength="255"><?= e($t['observaciones'] ?? '') ?></textarea>
                    </div>

                    <a class="btn btn-outline-secondary px-4" href="<?= $idTurno ? 'ver.php?id=' . $idTurno : 'listar.php' ?>">Volver</a>
                    <button class="btn btn-verde px-4"><?= $idTurno ? 'Guardar cambios' : 'Guardar turno' ?></button>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card resumen-turno shadow-sm">
                <div class="card-body p-4">
                    <h5 class="mb-3">Resumen del turno</h5>
                    <ul id="resumenServicios" class="ps-3 mb-2"></ul>
                    <p id="resumenVacio" class="text-muted">Todavía no seleccionaste servicios.</p>
                    <p class="text-muted small mb-0">Duración: <span id="duracion">0</span> minutos</p>
                    <p class="text-muted small">Horario: <span id="resumenHorario">-</span></p>
                    <hr>
                    <h5 class="mb-0">Total: <span id="total">$ 0</span></h5>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    // Datos iniciales (al editar vienen cargados; en un turno nuevo están vacíos)
    const idTurno = <?= $idTurno ?>;
    const profesionalOriginal = <?= json_encode((string)($t['id_profesional'] ?? '')) ?>;
    const fechaOriginal = <?= json_encode($t['fecha'] ?? '') ?>;
    const horaOriginal = <?= json_encode(substr($t['hora_inicio'] ?? '', 0, 5)) ?>;
    let serviciosMarcados = <?= json_encode(array_map('intval', $serviciosDelTurno)) ?>;
    // Precio con que se sacó el turno (los servicios que ya tenía no cambian de precio al modificarlo)
    const preciosGuardados = <?= json_encode((object)($preciosGuardados ?? [])) ?>;

    const selProfesional = document.getElementById('id_profesional');
    const inFecha = document.getElementById('fecha');
    const inHora = document.getElementById('hora_inicio');
    const listaServicios = document.getElementById('listaServicios');
    const listaHorarios = document.getElementById('listaHorarios');

    function plata(importe) {
        return '$ ' + Number(importe).toLocaleString('es-AR', {maximumFractionDigits: 0});
    }

    function mensaje(contenedor, texto, clase = 'text-muted') {
        contenedor.innerHTML = '';
        const p = document.createElement('p');
        p.className = clase;
        p.textContent = texto;
        contenedor.appendChild(p);
    }

    function serviciosElegidos() {
        return [...document.querySelectorAll('input[name="servicios[]"]:checked')];
    }

    // Trae los servicios del profesional elegido y los muestra como casillas
    function cargarServicios() {
        if (!selProfesional.value) {
            mensaje(listaServicios, 'Primero seleccioná un profesional.');
            calcularTotal();
            cargarHorarios();
            return;
        }
        mensaje(listaServicios, 'Cargando servicios...');

        fetch('servicios.php?id_profesional=' + encodeURIComponent(selProfesional.value))
            .then(r => r.json())
            .then(servicios => {
                listaServicios.innerHTML = '';
                if (servicios.length === 0) {
                    mensaje(listaServicios, 'Este profesional no tiene servicios cargados.');
                }
                servicios.forEach(s => {
                    if (selProfesional.value === profesionalOriginal && preciosGuardados[s.id_servicio] !== undefined) {
                        s.precio = preciosGuardados[s.id_servicio];
                    }
                    const div = document.createElement('div');
                    div.className = 'form-check';

                    const input = document.createElement('input');
                    input.className = 'form-check-input';
                    input.type = 'checkbox';
                    input.name = 'servicios[]';
                    input.id = 'serv' + s.id_servicio;
                    input.value = s.id_servicio;
                    input.dataset.precio = s.precio;
                    input.dataset.duracion = s.duracion_min;
                    input.dataset.nombre = s.nombre;
                    input.checked = serviciosMarcados.includes(Number(s.id_servicio));
                    input.addEventListener('change', () => { calcularTotal(); cargarHorarios(); });

                    const label = document.createElement('label');
                    label.className = 'form-check-label';
                    label.htmlFor = input.id;
                    label.textContent = `${s.nombre} - ${plata(s.precio)} (${s.duracion_min} minutos)`;

                    div.append(input, label);
                    listaServicios.appendChild(div);
                });
                calcularTotal();
                cargarHorarios();
            })
            .catch(() => mensaje(listaServicios, 'Error al cargar los servicios.', 'text-danger'));
    }

    // Suma precio y duración de los servicios marcados y arma el resumen
    function calcularTotal() {
        const elegidos = serviciosElegidos();
        const lista = document.getElementById('resumenServicios');
        let total = 0, duracion = 0;
        lista.innerHTML = '';

        elegidos.forEach(s => {
            total += Number(s.dataset.precio);
            duracion += Number(s.dataset.duracion);
            const li = document.createElement('li');
            li.textContent = `${s.dataset.nombre} - ${plata(s.dataset.precio)}`;
            lista.appendChild(li);
        });

        document.getElementById('resumenVacio').style.display = elegidos.length ? 'none' : 'block';
        document.getElementById('total').textContent = plata(total);
        document.getElementById('duracion').textContent = duracion;
        actualizarResumenHorario();
    }

    function actualizarResumenHorario() {
        document.getElementById('resumenHorario').textContent = inHora.value
            ? inHora.value + ' hs · ' + inFecha.value.split('-').reverse().join('/')
            : '-';
    }

    // Trae los horarios libres según profesional, fecha y duración de los servicios
    function cargarHorarios() {
        const elegidos = serviciosElegidos();
        if (!selProfesional.value || !inFecha.value || elegidos.length === 0) {
            mensaje(listaHorarios, 'Seleccioná un profesional, los servicios y una fecha.');
            inHora.value = '';
            actualizarResumenHorario();
            return;
        }

        const datos = new URLSearchParams({id_profesional: selProfesional.value, fecha: inFecha.value, excluir: idTurno});
        elegidos.forEach(s => datos.append('servicios[]', s.value));
        mensaje(listaHorarios, 'Buscando horarios...');

        fetch('horarios.php?' + datos)
            .then(r => r.json())
            .then(agenda => {
                // agenda = [{hora: '08:00', libre: true}, {hora: '08:20', libre: false}, ...]
                // Al editar, el horario que ya tenía el turno se sigue mostrando como libre
                if (idTurno && selProfesional.value === profesionalOriginal && inFecha.value === fechaOriginal
                    && horaOriginal && !agenda.some(h => h.hora === horaOriginal)) {
                    agenda.push({hora: horaOriginal, libre: true});
                    agenda.sort((a, b) => a.hora.localeCompare(b.hora));
                }
                const libres = agenda.filter(h => h.libre).map(h => h.hora);

                listaHorarios.innerHTML = '';
                if (!libres.includes(inHora.value)) {
                    inHora.value = '';
                }
                if (agenda.length === 0) {
                    mensaje(listaHorarios, 'No hay horarios ese día (se atiende de lunes a viernes). Probá con otra fecha.');
                } else if (libres.length === 0) {
                    mensaje(listaHorarios, 'Todos los horarios de ese día están ocupados. Probá con otra fecha.', 'text-danger');
                }
                agenda.forEach(h => {
                    const boton = document.createElement('button');
                    boton.type = 'button';
                    boton.textContent = h.hora;
                    if (!h.libre) {
                        // Ocupado: ya hay un turno de ese profesional en ese horario
                        boton.className = 'btn btn-ocupado me-2 mb-2';
                        boton.disabled = true;
                        boton.title = 'Horario ocupado';
                    } else {
                        boton.className = 'btn btn-horario me-2 mb-2';
                        if (h.hora === inHora.value) boton.classList.add('active');
                        boton.addEventListener('click', () => {
                            inHora.value = h.hora;
                            listaHorarios.querySelectorAll('.btn-horario').forEach(b => b.classList.remove('active'));
                            boton.classList.add('active');
                            actualizarResumenHorario();
                        });
                    }
                    listaHorarios.appendChild(boton);
                });
                if (agenda.length) {
                    const ref = document.createElement('div');
                    ref.className = 'form-text';
                    ref.textContent = 'Los horarios tachados están ocupados.';
                    listaHorarios.appendChild(ref);
                }
                actualizarResumenHorario();
            })
            .catch(() => mensaje(listaHorarios, 'Error al cargar los horarios.', 'text-danger'));
    }

    selProfesional.addEventListener('change', () => {
        serviciosMarcados = [];   // otro profesional: los servicios se eligen de nuevo
        cargarServicios();
    });
    inFecha.addEventListener('change', cargarHorarios);

    // Antes de enviar: tiene que haber al menos un servicio y un horario
    document.getElementById('formTurno').addEventListener('submit', function (ev) {
        if (serviciosElegidos().length === 0) {
            ev.preventDefault();
            alert('Elegí al menos un servicio.');
        } else if (!inHora.value) {
            ev.preventDefault();
            alert('Elegí un horario.');
        }
    });

    cargarServicios();
</script>
