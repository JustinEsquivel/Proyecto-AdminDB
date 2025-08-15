<?php include __DIR__ . '/../partials/header.php'; ?>

<?php
$nombre = $m['nombre'] ?? '';
$raza   = $m['raza']   ?? '';
$edad   = (int)($m['edad'] ?? 0);
$estado = $m['estado'] ?? 'Disponible';

$descripcion = $m['descripcion'] ?? '';
if ($descripcion instanceof OCILob) {
  $descripcion = $descripcion->load();
}

$foto = $m['foto'] ?? '';
if ($foto instanceof OCILob) {
  $foto = $foto->load();
}
$src = ($foto !== '' ? $foto : 'img/placeholder.jpg');

$idMascota = (int)($m['id'] ?? 0);
$rol = $_SESSION['rol'] ?? 'usuario';

$badgeClass = 'badge-secondary';
if ($estado === 'Disponible') $badgeClass = 'badge-success';
elseif ($estado === 'En Proceso') $badgeClass = 'badge-warning';
?>

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
            <td><?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?></td>
          </tr>
          <tr>
            <th class="text-muted">Raza:</th>
            <td><?= htmlspecialchars($raza, ENT_QUOTES, 'UTF-8') ?></td>
          </tr>
          <tr>
            <th class="text-muted">Edad:</th>
            <td><?= $edad ?></td>
          </tr>
          <tr>
            <th class="text-muted">Estado:</th>
            <td>
              <span class="badge <?= $badgeClass ?>">
                <?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>
              </span>
            </td>
          </tr>
          <tr>
            <th class="align-top text-muted">Descripción:</th>
            <td><?= nl2br(htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8')) ?></td>
          </tr>
          <tr>
            <th class="text-muted align-top">Foto:</th>
            <td>
              <img
                src="<?= htmlspecialchars($src, ENT_QUOTES, 'UTF-8') ?>"
                alt="Foto de <?= htmlspecialchars($nombre ?: 'Mascota', ENT_QUOTES, 'UTF-8') ?>"
                class="img-fluid rounded border"
                style="max-width: 320px;"
                onerror="this.onerror=null;this.src='img/placeholder.jpg';">
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="card-footer bg-white">
      <?php if ($rol === 'admin' || $rol === 'voluntario'): ?>
        <a href="mascotas.php?action=edit&id=<?= $idMascota ?>" class="btn btn-warning">Editar</a>
        <a href="mascotas.php" class="btn btn-secondary ml-2">Volver</a>
      <?php else: ?>
        <?php if ($estado === 'Disponible'): ?>
          <a href="adopciones.php?action=create&mascota_id=<?= $idMascota ?>" class="btn btn-success">
            <i class="fas fa-heart"></i> Adoptar
          </a>
        <?php endif; ?>
        <a href="mascotas_public.php" class="btn btn-secondary ml-2">Volver</a>
      <?php endif; ?>
    </div>
  </div>

</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
