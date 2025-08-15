<?php include __DIR__ . '/../partials/header.php'; ?>
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Editar Mascota</h1>
    <a href="mascotas.php" class="btn btn-secondary">Volver</a>
  </div>

  <?php
  $toStr = function($v) {
    if ($v instanceof OCILob) return $v->load();
    return (string)($v ?? '');
  };

  $id          = (int)($mascota['id'] ?? 0);
  $nombre      = $toStr($mascota['nombre'] ?? '');
  $raza        = $toStr($mascota['raza'] ?? '');
  $edad        = (int)($mascota['edad'] ?? 0);
  $estado      = $toStr($mascota['estado'] ?? 'Disponible');
  $foto        = $toStr($mascota['foto'] ?? '');
  $descripcion = $toStr($mascota['descripcion'] ?? '');
  ?>

  <div class="card shadow">
    <div class="card-body">
      <form action="mascotas.php?action=edit&id=<?= $id ?>" method="POST" novalidate>
        <div class="form-row">
          <div class="form-group col-md-6">
            <label>Nombre</label>
            <input type="text" name="nombre" class="form-control"
                   value="<?= htmlspecialchars($nombre) ?>" maxlength="100" required>
          </div>
          <div class="form-group col-md-6">
            <label>Raza</label>
            <input type="text" name="raza" class="form-control"
                   value="<?= htmlspecialchars($raza) ?>" maxlength="100" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group col-md-4">
            <label>Edad</label>
            <input type="number" name="edad" class="form-control"
                   value="<?= $edad ?>" min="0" step="1" required>
          </div>
          <div class="form-group col-md-4">
            <label>Estado</label>
            <select name="estado" class="form-control" required>
              <option value="Disponible"     <?= $estado==='Disponible'     ? 'selected' : '' ?>>Disponible</option>
              <option value="En Proceso"     <?= $estado==='En Proceso'     ? 'selected' : '' ?>>En Proceso</option>
              <option value="En observación" <?= $estado==='En observación' ? 'selected' : '' ?>>En observación</option>
              <option value="En tratamiento" <?= $estado==='En tratamiento' ? 'selected' : '' ?>>En tratamiento</option>
              <option value="Adoptado"       <?= $estado==='Adoptado'       ? 'selected' : '' ?>>Adoptado</option>
            </select>
          </div>
          <div class="form-group col-md-4">
            <label>URL Foto</label>
            <input type="text" name="foto" class="form-control"
                   value="<?= htmlspecialchars($foto) ?>" maxlength="255" placeholder="https://...">
          </div>
        </div>

        <div class="form-group">
          <label>Descripción</label>
          <textarea name="descripcion" class="form-control" rows="4" required><?= htmlspecialchars($descripcion) ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Actualizar</button>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
