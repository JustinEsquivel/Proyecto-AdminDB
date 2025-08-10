<?php
require_once __DIR__ . '/app/config/auth.php';
require_once __DIR__ . '/app/models/Mascota.php';
start_session_safe();

$mModel = new Mascota();
$disponibles = $mModel->disponibles();

include 'app/views/partials/header.php';
?>
<div class="container py-5">
  <h2 class="mb-4 text-center">Mascotas disponibles</h2>

  <?php if (!empty($_SESSION['mascotas_success'])): ?>
    <div class="alert alert-success">
      <?= $_SESSION['mascotas_success']; unset($_SESSION['mascotas_success']); ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($_SESSION['mascotas_error'])): ?>
    <div class="alert alert-danger">
      <?= $_SESSION['mascotas_error']; unset($_SESSION['mascotas_error']); ?>
    </div>
  <?php endif; ?>

  <?php if (!$disponibles): ?>
    <div class="alert alert-info">No hay mascotas disponibles por ahora.</div>
  <?php else: ?>
    <div class="row">
      <?php foreach ($disponibles as $m): ?>
        <div class="col-md-4 col-sm-12">
          <div class="card mb-3 h-100">
            <img src="<?= htmlspecialchars($m['foto'] ?: 'img/placeholder.jpg') ?>"
                 class="card-img-top imagen-mascota"
                 alt="Foto de <?= htmlspecialchars($m['nombre']) ?>"
                 loading="lazy"
                 onerror="this.onerror=null;this.src='img/placeholder.jpg';">
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($m['nombre']) ?></h5>
              <p class="card-text">
                Raza: <?= htmlspecialchars($m['raza']) ?><br>
                Edad: <?= (int)$m['edad'] ?> años
              </p>
              <span class="badge badge-success">Disponible</span>
            </div>

            <div class="card-footer bg-white border-0 d-flex justify-content-between">
              <a href="mascotas.php?action=detalles&id=<?= (int)$m['id'] ?>" class="btn btn-info btn-sm">
                <i class="fas fa-info-circle"></i> Detalles
              </a>

              <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (($_SESSION['rol'] ?? 'usuario') !== 'admin'): ?>
                  <a href="adopciones.php?action=create&mascota_id=<?= (int)$m['id'] ?>" class="btn btn-success btn-sm">
                    <i class="fas fa-heart"></i> Adoptar
                  </a>
                <?php else: ?>
                  <a href="mascotas.php?action=edit&id=<?= (int)$m['id'] ?>" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i> Editar
                  </a>
                <?php endif; ?>
              <?php else: ?>
                <a href="login.php" class="btn btn-outline-success btn-sm">Inicia sesión para adoptar</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<style>
.imagen-mascota{width:100%;height:220px;object-fit:cover;background:#f3f5f7}
</style>
<?php include 'app/views/partials/footer.php'; ?>
