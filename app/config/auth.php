<?php
function start_session_safe() {
  if (session_status() === PHP_SESSION_NONE) session_start();
}

function require_login() {
  start_session_safe();
  if (empty($_SESSION['user_id'])) {
    $_SESSION['auth_error'] = 'Debes iniciar sesión.';
    header('Location: login.php');
    exit();
  }
}

function home_url_by_role(): string {
  start_session_safe();
  $rol = $_SESSION['rol'] ?? 'usuario';
  return ($rol === 'admin' || $rol === 'voluntario') ? 'dashboard_admin.php' : 'dashboard.php';
}

function redirect_home_by_role(): void {
  header('Location: ' . home_url_by_role());
  exit();
}

function require_role($roles, string $redirect = null): void {
  start_session_safe();

  if (!is_array($roles)) $roles = [$roles];
  $userRole = $_SESSION['rol'] ?? null;

  if (!in_array($userRole, $roles, true)) {
    $_SESSION['auth_error'] = 'No tienes permisos para acceder a esta sección.';
    $dest = $redirect ?: home_url_by_role();
    header("Location: $dest");
    exit();
  }
}
