<?php include __DIR__ . '/../partials/header.php'; ?>
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Lista de Mascotas</h1>
    <?php if (($_SESSION['rol'] ?? 'usuario') === 'admin'): ?>
      <a href="mascotas.php?action=create" class="btn btn-primary"><i class="fas fa-plus"></i> Nueva Mascota</a>
    <?php endif; ?>
  </div>

  <?php if (!empty($_SESSION['mascotas_success'])): ?>
    <div class="alert alert-success"><?php echo $_SESSION['mascotas_success']; unset($_SESSION['mascotas_success']); ?></div>
  <?php endif; ?>
  <?php if (!empty($_SESSION['mascotas_error'])): ?>
    <div class="alert alert-danger"><?php echo $_SESSION['mascotas_error']; unset($_SESSION['mascotas_error']); ?></div>
  <?php endif; ?>

  <?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
  <?php endif; ?>

  <div class="table-responsive">
    <table class="table table-striped table-hover">
      <thead class="thead-dark">
        <tr>
          <th>Nombre</th>
          <th>Raza</th>
          <th>Edad</th>
          <th>Estado</th>
          <th>Propietario</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
      <?php if (!empty($mascotas)): foreach ($mascotas as $m): ?>
        <?php $estado = $m['estado'] ?? 'Disponible'; ?>
        <tr>
          <td><?= htmlspecialchars($m['nombre'] ?? '') ?></td>
          <td><?= htmlspecialchars($m['raza'] ?? '') ?></td>
          <td><?= (int)($m['edad'] ?? 0) ?> años</td>
          <td>
            <span class="badge
              <?= $estado==='Disponible' ? 'badge-success'
                 : ($estado==='En Proceso' ? 'badge-warning' : 'badge-secondary') ?>">
              <?= htmlspecialchars($estado) ?>
            </span>
          </td>
          <td><?= htmlspecialchars($m['propietario'] ?? '') ?></td>
          <td>
            <?php if (($_SESSION['rol'] ?? 'usuario') === 'admin'): ?>
              <a href="mascotas.php?action=detalles&id=<?= (int)($m['id'] ?? 0) ?>" class="btn btn-info btn-sm"><i class="fas fa-info-circle"></i> Detalles</a>
              <a href="mascotas.php?action=edit&id=<?= (int)($m['id'] ?? 0) ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Editar</a>
              <a href="mascotas.php?action=delete&id=<?= (int)($m['id'] ?? 0) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar?')"><i class="fas fa-trash"></i> Eliminar</a>
            <?php else: ?>
              <?php if ($estado === 'Disponible'): ?>
                <a href="adopciones.php?action=create&mascota_id=<?= (int)($m['id'] ?? 0) ?>" class="btn btn-success btn-sm">
                  <i class="fas fa-heart"></i> Adoptar
                </a>
              <?php else: ?>
                <span class="text-muted">No disponible</span>
              <?php endif; ?>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; else: ?>
        <tr><td colspan="6" class="text-center">No hay mascotas registradas</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
