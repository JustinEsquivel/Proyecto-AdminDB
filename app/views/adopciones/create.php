<?php include __DIR__ . '/../partials/header.php'; ?>
<div class="container mt-4" style="max-width:760px;">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Iniciar proceso de adopción</h1>
    <a href="mascotas_public.php" class="btn btn-secondary">Volver</a>
  </div>

  <div class="card shadow">
    <div class="card-body">
      <p class="mb-3">
        Estás solicitando adoptar a <strong><?= htmlspecialchars($mascota_nombre) ?></strong>.
        Al confirmar, <strong>registraremos tu solicitud</strong> y te contactaremos con los siguientes pasos.
      </p>

      <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle"></i>
        Tu solicitud se asociará a tu cuenta (<strong><?= htmlspecialchars($_SESSION['email'] ?? 'correo no disponible') ?></strong>).
      </div>

      <form method="POST">
        <button class="btn btn-success">
          <i class="fas fa-paper-plane"></i> Confirmar solicitud
        </button>
        <a href="mascotas_public.php" class="btn btn-outline-secondary ml-2">Cancelar</a>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
