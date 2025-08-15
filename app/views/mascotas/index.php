<?php include __DIR__ . '/../partials/header.php'; ?>

<?php
$toStr = function($v) {
  if ($v instanceof OCILob) return $v->load();
  return (string)($v ?? '');
};

$badgeForEstado = function($estadoRaw) use ($toStr) {
  $estado = trim($toStr($estadoRaw));
  if ($estado === '') return 'badge-secondary';

  $up = mb_strtoupper($estado, 'UTF-8');
  $upNoTilde = str_replace(['Á','É','Í','Ó','Ú'], ['A','E','I','O','U'], $up);

  if ($upNoTilde === 'DISPONIBLE')               return 'badge-success';
  if ($upNoTilde === 'EN PROCESO')               return 'badge-warning';
  if ($upNoTilde === 'EN OBSERVACION')           return 'badge-info';
  if ($upNoTilde === 'EN TRATAMIENTO')           return 'badge-primary';
  if ($upNoTilde === 'ADOPTADO')                 return 'badge-secondary';

  return 'badge-secondary';
};
?>

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Lista de Mascotas</h1>
    <?php if (($_SESSION['rol'] ?? 'usuario') === 'admin'): ?>
      <a href="mascotas.php?action=create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nueva Mascota
      </a>
    <?php endif; ?>
  </div>

  <?php if (!empty($_SESSION['mascotas_success'])): ?>
    <div class="alert alert-success">
      <?php echo $_SESSION['mascotas_success']; unset($_SESSION['mascotas_success']); ?>
    </div>
  <?php endif; ?>
  <?php if (!empty($_SESSION['mascotas_error'])): ?>
    <div class="alert alert-danger">
      <?php echo $_SESSION['mascotas_error']; unset($_SESSION['mascotas_error']); ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
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
        <?php
          $id          = (int)($m['id'] ?? 0);
          $nombre      = $toStr($m['nombre'] ?? '');
          $raza        = $toStr($m['raza'] ?? '');
          $edad        = (int)($m['edad'] ?? 0);
          $estado      = $toStr($m['estado'] ?? 'Disponible');
          $propietario = $toStr($m['propietario'] ?? '');
          $badgeClass  = $badgeForEstado($estado);
        ?>
        <tr>
          <td><?= htmlspecialchars($nombre) ?></td>
          <td><?= htmlspecialchars($raza) ?></td>
          <td><?= $edad ?> años</td>
          <td>
            <span class="badge <?= $badgeClass ?>">
              <?= htmlspecialchars($estado) ?>
            </span>
          </td>
          <td><?= htmlspecialchars($propietario) ?></td>
          <td>
            <?php if (($_SESSION['rol'] ?? 'usuario') === 'admin'): ?>
              <a href="mascotas.php?action=detalles&id=<?= $id ?>" class="btn btn-info btn-sm">
                <i class="fas fa-info-circle"></i> Detalles
              </a>
              <a href="mascotas.php?action=edit&id=<?= $id ?>" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Editar
              </a>
              <a href="mascotas.php?action=delete&id=<?= $id ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar?')">
                <i class="fas fa-trash"></i> Eliminar
              </a>
            <?php else: ?>
              <?php if (mb_strtoupper($estado, 'UTF-8') === 'DISPONIBLE'): ?>
                <a href="adopciones.php?action=create&mascota_id=<?= $id ?>" class="btn btn-success btn-sm">
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
