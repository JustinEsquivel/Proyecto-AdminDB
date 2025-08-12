<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/Reportes.php';

class ReportesController {
  public function index() {
    require_login();
    $m = new Reportes();
    $reportes = $m->all();
    require 'app/views/reportes/index.php';
  }

  public function create() {
    require_login();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $data = [
        'mascota'      => $_POST['mascota'] ?? '',
        'provincia'        => $_POST['provincia'] ?? '',
        'canton'        => $_POST['canton'] ?? '', 
        'distrito'        => $_POST['distrito'] ?? '',          
        'detalles' => $_POST['detalles'] ?? '',
        'fecha'        => $_POST['fecha'] ?? null,       
        'usuario'     => (int)($_SESSION['user_id']),
        'mascota'     => (int)($_SESSION['user_id'])
      ];
      $m = new Reportes();
      if ($m->create($data)) {
        $_SESSION['success'] = 'Reporte creado correctamente';
        header('Location: reportes.php');
      } else {
        $_SESSION['error'] = 'Error: ' . $m->getError();
        header('Location: reportes.php?action=create');
      }
      exit();
    }
    require 'app/views/reportes/create.php';
  }

  public function edit(int $id) {
    require_role(['admin']);
    $m = new Reportes();
    $reporte = $m->find($id);
    if (!$reporte) { header('Location: reportes.php'); exit(); }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $data = [
        'mascota'      => $_POST['mascota'] ?? '',
        'provincia'   => $_POST['provincia'] ?? '',
        'canton'      => $_POST['canton'] ?? '',
        'distrito'    => $_POST['distrito'] ?? '',
        'detalles' => $_POST['detalles'] ?? '',
        'fecha'        => $_POST['fecha'] ?? null,             
        'usuario'     => (int)($reporte['USUARIO'] ?? $reporte['usuario'] ?? 0)
      ];
      if ($m->update($id, $data)) {
        $_SESSION['success'] = 'Reporte actualizado correctamente';
      } else {
        $_SESSION['error'] = 'Error: ' . $m->getError();
      }
      header('Location: reportes.php');
      exit();
    }
    require 'app/views/reportes/edit.php';
  }

  public function delete(int $id) {
    require_role(['admin']);
    $m = new Reportes();
    if ($m->delete($id)) {
      $_SESSION['success'] = 'Reporte eliminado correctamente';
    } else {
      $_SESSION['error'] = 'Error: ' . $m->getError();
    }
    header('Location: reportes.php');
    exit();
  }

  public function detalles(int $id) {
    require_once __DIR__ . '/../models/Reportes.php';
    $model = new Reportes();
    $reporte = method_exists($model,'findById') ? $model->findById($id) : $model->find($id);

    if (!$reporte) {
      $_SESSION['reportes_error'] = 'Reporte no encontrado.';
      header('Location: reportes.php'); exit();
    }

    $m = $reporte;
    require __DIR__ . '/../views/reportes/detalles.php';
  }
}
