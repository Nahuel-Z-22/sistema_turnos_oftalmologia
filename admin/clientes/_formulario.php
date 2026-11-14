<?php
// Campos del formulario de paciente (tabla clientes). Se usa en nuevo.php y editar.php
if (!isset($c)) exit; // no se abre directamente
?>
<?= csrf_input() ?>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Apellido</label>
        <input class="form-control" name="apellido" maxlength="100" value="<?= e($c['apellido'] ?? '') ?>" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Nombre</label>
        <input class="form-control" name="nombre" maxlength="100" value="<?= e($c['nombre'] ?? '') ?>" required>
    </div>
</div>
<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">DNI</label>
        <input class="form-control" name="dni" maxlength="15" pattern="[0-9]{6,15}" title="Solo números" value="<?= e($c['dni'] ?? '') ?>" required>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Teléfono</label>
        <input class="form-control" name="telefono" maxlength="30" value="<?= e($c['telefono'] ?? '') ?>">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Fecha de nacimiento</label>
        <input type="date" class="form-control" name="fecha_nacimiento" value="<?= e($c['fecha_nacimiento'] ?? '') ?>">
    </div>
</div>
<div class="row">
    <div class="col-md-9 mb-4">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" name="email" maxlength="150" value="<?= e($c['email'] ?? '') ?>">
    </div>
    <div class="col-md-3 mb-4 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="activo" value="1" id="activo" <?= ($c['activo'] ?? 1) ? 'checked' : '' ?>>
            <label class="form-check-label" for="activo">Activo</label>
        </div>
    </div>
</div>
