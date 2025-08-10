<?php
require_once __DIR__ . '/app/controllers/AuthController.php';
require_once __DIR__ . '/app/config/auth.php'; 
start_session_safe();

$auth = new AuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth->login();
    exit;
}

include 'app/views/partials/header.php';
?>
<div class="container mt-5" style="max-width:480px;">
  <?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
  <?php endif; ?>
  <div class="card shadow">
    <div class="card-body">
      <h3 class="mb-4">Iniciar sesión</h3>
      <form method="POST">
        <div class="form-group">
          <label>Email</label>
          <input class="form-control" type="email" name="email" required>
        </div>
        <div class="form-group">
          <label>Contraseña</label>
          <input class="form-control" type="password" name="password" required>
        </div>
        <button class="btn btn-success btn-block">Entrar</button>
      </form>
    </div>
  </div>
</div>
<?php include 'app/views/partials/footer.php'; ?>
