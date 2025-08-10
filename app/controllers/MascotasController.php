<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/Mascota.php';

class MascotasController {
  public function index() {
    require_login();
    $m = new Mascota();
    $mascotas = $m->all();
    require 'app/views/mascotas/index.php';
  }

  public function create() {
    require_login();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $data = [
        'nombre'      => $_POST['nombre'] ?? '',
        'raza'        => $_POST['raza'] ?? '',
        'edad'        => (int)($_POST['edad'] ?? 0),
        'descripcion' => $_POST['descripcion'] ?? '',
        'foto'        => $_POST['foto'] ?? null,
        'estado'      => $_POST['estado'] ?? 'Disponible',
        'usuario'     => (int)($_SESSION['user_id'])
      ];
      $m = new Mascota();
      if ($m->create($data)) {
        $_SESSION['success'] = 'Mascota creada correctamente';
        header('Location: mascotas.php');
      } else {
        $_SESSION['error'] = 'Error: ' . $m->getError();
        header('Location: mascotas.php?action=create');
      }
      exit();
    }
    require 'app/views/mascotas/create.php';
  }

  public function edit(int $id) {
    require_role(['admin']);
    $m = new Mascota();
    $mascota = $m->find($id);
    if (!$mascota) { header('Location: mascotas.php'); exit(); }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $data = [
        'nombre'      => $_POST['nombre'] ?? '',
        'raza'        => $_POST['raza'] ?? '',
        'edad'        => (int)($_POST['edad'] ?? 0),
        'descripcion' => $_POST['descripcion'] ?? '',
        'foto'        => $_POST['foto'] ?? null,
        'estado'      => $_POST['estado'] ?? 'Disponible',
        'usuario'     => (int)($mascota['USUARIO'] ?? $mascota['usuario'] ?? 0)
      ];
      if ($m->update($id, $data)) {
        $_SESSION['success'] = 'Mascota actualizada';
      } else {
        $_SESSION['error'] = 'Error: ' . $m->getError();
      }
      header('Location: mascotas.php');
      exit();
    }
    require 'app/views/mascotas/edit.php';
  }

  public function delete(int $id) {
    require_role(['admin']);
    $m = new Mascota();
    if ($m->delete($id)) {
      $_SESSION['success'] = 'Mascota eliminada';
    } else {
      $_SESSION['error'] = 'Error: ' . $m->getError();
    }
    header('Location: mascotas.php');
    exit();
  }

  public function detalles(int $id) {
    require_once __DIR__ . '/../models/Mascota.php';
    $model = new Mascota();
    $mascota = method_exists($model,'findById') ? $model->findById($id) : $model->find($id);

    if (!$mascota) {
      $_SESSION['mascotas_error'] = 'Mascota no encontrada.';
      header('Location: mascotas.php'); exit();
    }

    $m = $mascota;
    require __DIR__ . '/../views/mascotas/detalles.php';
  }
}
