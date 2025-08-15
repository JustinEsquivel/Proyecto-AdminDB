<?php
require_once __DIR__ . '/app/controllers/AuthController.php';
require_once __DIR__ . '/app/config/auth.php';
start_session_safe();

$errors = $_SESSION['form_errors'] ?? [];
$old    = $_SESSION['old'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['old']);

$auth = new AuthController();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $auth->register();
  exit;
}

include 'app/views/partials/header.php';
?>
<div class="container mt-5" style="max-width:560px;">

  <?php if ($errors): ?>
    <div class="alert alert-warning">
      <strong>Revisa el formulario:</strong>
      <ul class="mb-0">
        <?php foreach ($errors as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="card shadow">
    <div class="card-body">
      <h3 class="mb-4">Crear cuenta</h3>
      <form id="registerForm" method="POST" novalidate>
        <?php  ?>
        <div class="form-row">
          <div class="form-group col-md-6">
            <label>Nombre</label>
            <input class="form-control" type="text" name="nombre" required
                   value="<?= htmlspecialchars($old['nombre'] ?? '') ?>">
            <div class="invalid-feedback">Ingresa tu nombre.</div>
          </div>
          <div class="form-group col-md-6">
            <label>Apellido</label>
            <input class="form-control" type="text" name="apellido" required
                   value="<?= htmlspecialchars($old['apellido'] ?? '') ?>">
            <div class="invalid-feedback">Ingresa tu apellido.</div>
          </div>
        </div>

        <div class="form-group">
          <label>Email</label>
          <input class="form-control" type="email" name="email" required
                 pattern="^[^@\s]+@[^@\s]+\.[A-Za-z]{2,}$"
                 value="<?= htmlspecialchars($old['email'] ?? '') ?>">
          <small class="form-text text-muted">Debe tener formato válido (ej: nombre@dominio.com).</small>
          <div class="invalid-feedback">Correo inválido. Asegúrate de incluir “@” y el dominio.</div>
        </div>

        <div class="form-row">
          <div class="form-group col-md-6">
            <label>Teléfono</label>
            <input class="form-control" type="text" name="telefono" required
                   minlength="8" maxlength="20"
                   value="<?= htmlspecialchars($old['telefono'] ?? '') ?>">
            <small class="form-text text-muted">Debe tener al menos 8 caracteres.</small>
            <div class="invalid-feedback">Ingresa tu teléfono.</div>
          </div>
          <div class="form-group col-md-6">
            <label>Contraseña</label>
            <input class="form-control" type="password" name="password" required minlength="6">
            <small id="pwdHelp" class="form-text text-muted">Mínimo 6 caracteres.</small>
            <div class="invalid-feedback">La contraseña debe tener al menos 6 caracteres.</div>
          </div>
        </div>

        <button class="btn btn-success btn-block">Registrarme</button>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('registerForm');
  const emailInput = form.querySelector('input[name="email"]');
  const passInput  = form.querySelector('input[name="password"]');

  const showInvalid = (input, cond) => {
    if (cond) input.classList.add('is-invalid'); else input.classList.remove('is-invalid');
  };

  emailInput.addEventListener('input', () => {
    const re = new RegExp(emailInput.getAttribute('pattern'));
    showInvalid(emailInput, !re.test(emailInput.value));
  });

  passInput.addEventListener('input', () => {
    showInvalid(passInput, passInput.value.length < 6);
  });

  form.addEventListener('submit', (e) => {
    let invalid = false;
    const re = new RegExp(emailInput.getAttribute('pattern'));
    if (!re.test(emailInput.value)) { showInvalid(emailInput, true); invalid = true; }
    if (passInput.value.length < 6)  { showInvalid(passInput, true); invalid = true; }

    form.querySelectorAll('[required]').forEach(inp => {
      if (!inp.value.trim()) { inp.classList.add('is-invalid'); invalid = true; }
      else { inp.classList.remove('is-invalid'); }
    });

    if (invalid) { e.preventDefault(); e.stopPropagation(); }
  });
});
</script>

<?php include 'app/views/partials/footer.php'; ?>
