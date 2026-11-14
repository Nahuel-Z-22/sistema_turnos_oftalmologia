<?php
// Campos del formulario de servicio. Se usa en nuevo.php y editar.php
if (!isset($s)) exit; // no se abre directamente
?>
<?= csrf_input() ?>
<div class="mb-3">
    <label class="form-label">Nombre</label>
    <input class="form-control" name="nombre" maxlength="100" value="<?= e($s['nombre'] ?? '') ?>" required>
</div>
<div class="mb-3">
    <label class="form-label">Descripción</label>
    <textarea class="form-control" name="descripcion" rows="3" maxlength="255"><?= e($s['descripcion'] ?? '') ?></textarea>
</div>
<div class="row">
    <div class="col-md-4 mb-4">
        <label class="form-label">Precio ($)</label>
        <input type="number" class="form-control" name="precio" min="0" step="0.01" value="<?= e($s['precio'] ?? '') ?>" required>
    </div>
    <div class="col-md-4 mb-4">
        <label class="form-label">Duración (minutos)</label>
        <input type="number" class="form-control" name="duracion_min" min="5" step="5" value="<?= e($s['duracion_min'] ?? 20) ?>" required>
    </div>
    <div class="col-md-4 mb-4 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="activo" value="1" id="activo" <?= ($s['activo'] ?? 1) ? 'checked' : '' ?>>
            <label class="form-check-label" for="activo">Activo</label>
        </div>
    </div>
</div>
