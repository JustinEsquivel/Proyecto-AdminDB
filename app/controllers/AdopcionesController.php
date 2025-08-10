<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/Mascota.php';
require_once __DIR__ . '/../models/Adopcion.php';

class AdopcionesController {
  public function create() {
    require_login();

    $mascotaId = (int)($_GET['mascota_id'] ?? 0);
    $mModel = new Mascota();
    $mascota = $mModel->find($mascotaId);

    $mx = is_array($mascota) ? array_change_key_case($mascota, CASE_LOWER) : null;

    if (!$mx || ($mx['estado'] ?? null) !== 'Disponible') {
      $_SESSION['mascotas_error'] = 'La mascota no está disponible para adopción.';
      header('Location: mascotas_public.php');
      exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $aModel = new Adopcion();
      $ok = $aModel->crearSolicitud([
        'usuario'     => (int)($_SESSION['user_id']),
        'mascota'     => $mascotaId,
        'motivo'      => trim($_POST['motivo'] ?? ''),
        'experiencia' => trim($_POST['experiencia'] ?? ''),
        'contacto'    => trim($_POST['contacto'] ?? ($_SESSION['email'] ?? '')),
      ]);

      if ($ok) {
        $_SESSION['mascotas_success'] = 'Solicitud recibida. Te enviaremos los detalles por correo.';
        header('Location: mascotas_public.php');
      } else {
        $_SESSION['adopciones_error'] = 'No se pudo registrar la solicitud: ' . $aModel->getError();
        header('Location: adopciones.php?action=create&mascota_id=' . $mascotaId);
      }
      exit();
    }

    $mascota_nombre = $mx['nombre'] ?? '';
    require __DIR__ . '/../views/adopciones/create.php';
  }
}
