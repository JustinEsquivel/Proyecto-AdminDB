<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/Eventos.php';

class EventosController {
  public function index() {
    require_login();
    $m = new Eventos();
    $eventos = $m->all();
    require 'app/views/eventos/index.php';
  }

  public function create() {
    require_login();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $data = [
        'nombre'      => $_POST['nombre'] ?? '',
        'ubicacion'        => $_POST['ubicacion'] ?? '',
        'responsable'        => (int)($_POST['responsable'] ?? 0),
        'descripcion' => $_POST['descripcion'] ?? '',
        'fecha'        => $_POST['fecha'] ?? null,
        'estado'      => $_POST['estado'] ?? 'Disponible',
        'tipo'      => $_POST['tipo'] ?? '',
        'usuario'     => (int)($_SESSION['user_id'])
      ];
      $m = new Eventos();
      if ($m->create($data)) {
        $_SESSION['success'] = 'Evento creado correctamente';
        header('Location: eventos.php');
      } else {
        $_SESSION['error'] = 'Error: ' . $m->getError();
        header('Location: eventos.php?action=create');
      }
      exit();
    }
    require 'app/views/eventos/create.php';
  }

  public function edit(int $id) {
    require_role(['admin']);
    $m = new Eventos();
    $evento = $m->find($id);
    if (!$evento) { header('Location: eventos.php'); exit(); }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $data = [
        'nombre'      => $_POST['nombre'] ?? '',
        'ubicacion'        => $_POST['ubicacion'] ?? '',
        'responsable'        => (int)($_POST['responsable'] ?? 0),
        'descripcion' => $_POST['descripcion'] ?? '',
        'fecha'        => $_POST['fecha'] ?? null,
        'estado'      => $_POST['estado'] ?? 'Disponible',
        'tipo'      => $_POST['tipo'] ?? '',
        'usuario'     => (int)($evento['USUARIO'] ?? $evento['usuario'] ?? 0)
      ];
      if ($m->update($id, $data)) {
        $_SESSION['success'] = 'Evento actualizado correctamente';
      } else {
        $_SESSION['error'] = 'Error: ' . $m->getError();
      }
      header('Location: eventos.php');
      exit();
    }
    require 'app/views/eventos/edit.php';
  }

  public function delete(int $id) {
    require_role(['admin']);
    $m = new Eventos();
    if ($m->delete($id)) {
      $_SESSION['success'] = 'Evento eliminado correctamente';
    } else {
      $_SESSION['error'] = 'Error: ' . $m->getError();
    }
    header('Location: eventos.php');
    exit();
  }

  public function detalles(int $id) {
    require_once __DIR__ . '/../models/Eventos.php';
    $model = new Eventos();
    $evento = method_exists($model,'findById') ? $model->findById($id) : $model->find($id);

    if (!$evento) {
      $_SESSION['eventos_error'] = 'Evento no encontrado.';
      header('Location: eventos.php'); exit();
    }

    $m = $evento;
    require __DIR__ . '/../views/eventos/detalles.php';
  }
}
