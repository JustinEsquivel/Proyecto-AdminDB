<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/Usuario.php';

class AuthController {

  public function login() {
  start_session_safe();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_SESSION['user_id'])) {
      redirect_home_by_role();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $email = trim($_POST['email'] ?? '');
      $pass  = $_POST['password'] ?? '';

      $userModel = new Usuario();
      $user = $userModel->findByEmail($email);

      // Normaliza claves por si vienen en MAYÚSCULAS desde Oracle
      $u = is_array($user) ? array_change_key_case($user, CASE_LOWER) : null;

      // Verificación: admite hash bcrypt y (temporal) texto plano
      $okPass = false;
      if ($u && isset($u['password'])) {
        $stored = (string)$u['password'];
        $okPass = ($pass === $stored) || password_verify($pass, $stored);
      }

      if ($u && $okPass) {
        session_regenerate_id(true);

        $_SESSION['user_id'] = (int)($u['id'] ?? 0);
        $_SESSION['nombre']  = $u['nombre'] ?? '';
        $_SESSION['email']   = $u['email']  ?? '';

        // --------- MAPEO DE ROL ROBUSTO ---------
        $rolNombre = isset($u['rol_nombre']) ? trim((string)$u['rol_nombre']) : '';
        $rolId     = isset($u['rol']) ? (int)$u['rol'] : null;

        if (strcasecmp($rolNombre, 'Administrador') === 0 || $rolId === 1) {
          $_SESSION['rol'] = 'admin';
        } elseif (strcasecmp($rolNombre, 'Voluntario') === 0 || $rolId === 3) {
          $_SESSION['rol'] = 'voluntario';
        } else {
          $_SESSION['rol'] = 'usuario';
        }
        // ----------------------------------------

        redirect_home_by_role();
      }

      $_SESSION['error'] = 'Credenciales inválidas';
      header('Location: login.php');
      exit();
    }
  }

  public function register() {
    start_session_safe();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      header('Location: register.php'); exit();
    }

    $nombre   = trim($_POST['nombre']   ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $password = $_POST['password']      ?? '';

    $_SESSION['old'] = compact('nombre','apellido','email','telefono');

    $errors = [];
    if ($nombre === '')   $errors[] = 'El nombre es obligatorio.';
    if ($apellido === '') $errors[] = 'El apellido es obligatorio.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'El correo no tiene un formato válido (ej: nombre@dominio.com).';
    if (!preg_match('/^[^@\s]+@[^@\s]+\.com$/i', $email)) $errors[] = 'El correo debe terminar en .com.';
    if (strlen($password) < 4) $errors[] = 'La contraseña debe tener al menos 4 caracteres.';
    if ($telefono === '') $errors[] = 'El teléfono es obligatorio.';
    if (strlen($telefono) < 8) $errors[] = 'El teléfono debe tener 8 caracteres.';

    $userModel = new Usuario();
    if (empty($errors) && $userModel->findByEmail($email)) $errors[] = 'El correo ya está registrado.';

    if (!empty($errors)) {
      $_SESSION['form_errors'] = $errors;
      header('Location: register.php');
      return;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $ok = $userModel->create([
      'nombre'   => $nombre,
      'apellido' => $apellido,
      'email'    => $email,
      'password' => $hash,
      'telefono' => $telefono,
      'rol'      => 2 // Usuario
    ]);

    if (!$ok) {
      $_SESSION['form_errors'] = [
        'No se pudo crear la cuenta. Error: ' . $userModel->getError()
      ];
      header('Location: register.php');
      return;
    }

    $user = $userModel->findByEmail($email);
    $u = is_array($user) ? array_change_key_case($user, CASE_LOWER) : null;

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)($u['id'] ?? 0);
    $_SESSION['nombre']  = $u['nombre'] ?? '';
    $_SESSION['email']   = $u['email']  ?? '';
    $_SESSION['rol']     = 'usuario';

    unset($_SESSION['old']);
    $_SESSION['success'] = 'Cuenta creada con éxito. ¡Bienvenido!';

    redirect_home_by_role();
  }

  public function logout() {
    start_session_safe();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
      $p = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    header('Location: login.php');
    exit();
  }
}
