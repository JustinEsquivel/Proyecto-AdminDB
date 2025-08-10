<?php include __DIR__ . '/../partials/header.php'; ?>
<div class="container mt-4" style="max-width: 960px;">

  <div class="card shadow-sm">
    <div class="card-header text-white" style="background:#14b8d4;">
      <strong>Detalles de Mascota</strong>
    </div>

    <div class="card-body p-0">
      <table class="table mb-0">
        <tbody>
          <tr>
            <th class="w-25 text-muted">Nombre:</th>
            <td><?= htmlspecialchars($m['nombre'] ?? '') ?></td>
          </tr>
          <tr>
            <th class="text-muted">Raza:</th>
            <td><?= htmlspecialchars($m['raza'] ?? '') ?></td>
          </tr>
          <tr>
            <th class="text-muted">Edad:</th>
            <td><?= (int)($m['edad'] ?? 0) ?></td>
          </tr>
          <tr>
            <th class="text-muted">Estado:</th>
            <td>
              <?php $estado = $m['estado'] ?? 'Disponible'; ?>
              <span class="badge <?= ($estado==='Disponible'?'badge-success':($estado==='En Proceso'?'badge-warning':'badge-secondary')) ?>">
                <?= htmlspecialchars($estado) ?>
              </span>
            </td>
          </tr>
          <tr>
            <th class="align-top text-muted">Descripción:</th>
            <td><?= nl2br(htmlspecialchars($m['descripcion'] ?? '')) ?></td>
          </tr>
          <tr>
            <th class="text-muted align-top">Foto:</th>
            <td>
              <?php $src = $m['foto'] ?: 'img/placeholder.jpg'; ?>
              <img
                src="<?= htmlspecialchars($src) ?>"
                alt="Foto de <?= htmlspecialchars($m['nombre'] ?? 'Mascota') ?>"
                class="img-fluid rounded border"
                style="max-width: 320px;"
                onerror="this.onerror=null;this.src='img/placeholder.jpg';">
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="card-footer bg-white">
      <?php if (($_SESSION['rol'] ?? 'usuario') === 'admin' || ($_SESSION['rol'] ?? '') === 'voluntario'): ?>
        <a href="mascotas.php?action=edit&id=<?= (int)($m['id'] ?? 0) ?>" class="btn btn-warning">Editar</a>
        <a href="mascotas.php" class="btn btn-secondary ml-2">Volver</a>
      <?php else: ?>
        <?php if (($m['estado'] ?? '') === 'Disponible'): ?>
          <a href="adopciones.php?action=create&mascota_id=<?= (int)($m['id'] ?? 0) ?>" class="btn btn-success">
            <i class="fas fa-heart"></i> Adoptar
          </a>
        <?php endif; ?>
        <a href="mascotas_public.php" class="btn btn-secondary ml-2">Volver</a>
      <?php endif; ?>
    </div>
  </div>

</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
