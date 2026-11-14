<?php
// Campos del formulario de profesional (se usa en nuevo.php y editar.php)
// Variables esperadas: $p (datos del profesional), $especialidades, $servicios, $serviciosDelProfesional (array de id)
if (!isset($especialidades, $servicios, $serviciosDelProfesional)) exit; // no se abre directamente
?>
<?= csrf_input() ?>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Apellido</label>
        <input class="form-control" name="apellido" maxlength="100" value="<?= e($p['apellido'] ?? '') ?>" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Nombre</label>
        <input class="form-control" name="nombre" maxlength="100" value="<?= e($p['nombre'] ?? '') ?>" required>
    </div>
</div>
<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">DNI</label>
        <input class="form-control" name="dni" maxlength="15" pattern="[0-9]{6,15}" title="Solo números" value="<?= e($p['dni'] ?? '') ?>" required>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Matrícula</label>
        <input class="form-control" name="matricula" maxlength="30" value="<?= e($p['matricula'] ?? '') ?>" required>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Especialidad</label>
        <select class="form-select" name="id_especialidad" required>
            <option value="">Seleccione...</option>
            <?php foreach ($especialidades as $esp) { ?>
                <option value="<?= $esp['id_especialidad'] ?>" <?= ($p['id_especialidad'] ?? null) == $esp['id_especialidad'] ? 'selected' : '' ?>><?= e($esp['nombre']) ?></option>
            <?php } ?>
        </select>
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Teléfono</label>
        <input class="form-control" name="telefono" maxlength="30" value="<?= e($p['telefono'] ?? '') ?>">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" name="email" maxlength="150" value="<?= e($p['email'] ?? '') ?>">
    </div>
</div>
<div class="row">
    <div class="col-md-9 mb-4">
        <label class="form-label">Servicios que realiza</label>
        <select class="form-select" name="servicios[]" multiple size="6" required>
            <?php foreach ($servicios as $s) { ?>
                <option value="<?= $s['id_servicio'] ?>" <?= in_array($s['id_servicio'], $serviciosDelProfesional) ? 'selected' : '' ?>>
                    <?= e($s['nombre']) ?>
                </option>
            <?php } ?>
        </select>
        <div class="form-text">Mantené Ctrl apretado para elegir más de uno.</div>
    </div>
    <div class="col-md-3 mb-4 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="activo" value="1" id="activo" <?= ($p['activo'] ?? 1) ? 'checked' : '' ?>>
            <label class="form-check-label" for="activo">Activo</label>
        </div>
    </div>
</div>
