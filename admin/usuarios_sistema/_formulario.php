<?php
// Campos del formulario de usuario del sistema. Se usa en nuevo.php y editar.php
// Variables esperadas: $u (datos del usuario) y $roles (filas de la tabla roles)
if (!isset($u, $roles)) exit; // no se abre directamente
$esNuevo = empty($u['id_usuario']);
?>
<?= csrf_input() ?>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Usuario</label>
        <input class="form-control" name="usuario" maxlength="50" value="<?= e($u['usuario'] ?? '') ?>" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Nombre completo</label>
        <input class="form-control" name="nombre_completo" maxlength="150" value="<?= e($u['nombre_completo'] ?? '') ?>" required>
    </div>
</div>
<div class="mb-3">
    <label class="form-label">Email</label>
    <input type="email" class="form-control" name="email" maxlength="150" value="<?= e($u['email'] ?? '') ?>">
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Contraseña</label>
        <input type="password" class="form-control" name="clave" minlength="4" autocomplete="new-password" <?= $esNuevo ? 'required' : '' ?>>
        <?php if (!$esNuevo) { ?><div class="form-text">Dejala vacía para no cambiarla.</div><?php } ?>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Rol</label>
        <select class="form-select" name="id_rol" required>
            <?php foreach ($roles as $r) { ?>
                <option value="<?= $r['id_rol'] ?>" <?= ($u['id_rol'] ?? null) == $r['id_rol'] ? 'selected' : '' ?>><?= e($r['nombre']) ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="col-md-3 mb-3 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="activo" value="1" id="activo" <?= ($u['activo'] ?? 1) ? 'checked' : '' ?>>
            <label class="form-check-label" for="activo">Activo</label>
        </div>
    </div>
</div>
