<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Mascota.php';

class AdopcionesController {

  public function create() {
    require_login();

    $mascotaId = (int)($_GET['mascota_id'] ?? 0);

    // 1) Obtener la mascota y validar estado
    $mModel = new Mascota();
    $mascota = $mModel->find($mascotaId);

    if (!$mascota) {
      $_SESSION['mascotas_error'] = 'Mascota no encontrada.';
      header('Location: mascotas_public.php'); exit();
    }

    // Normalizamos estado para validar "DISPONIBLE"
    $estadoUp = mb_strtoupper((string)($mascota['estado'] ?? ''), 'UTF-8');
    if ($estadoUp !== 'DISPONIBLE') {
      $_SESSION['mascotas_error'] = 'La mascota no está disponible para adopción.';
      header('Location: mascotas_public.php'); exit();
    }

    // 2) Si se confirmó (POST), registrar adopción y cambiar estado
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $db = Database::connect();

      try {
        // Iniciar transacción (soporta PDO y OciAdapter)
        if ($db instanceof OciAdapter) { $db->beginTransaction(); }
        else if ($db instanceof PDO)   { $db->beginTransaction(); }

        // 2.1 Insertar solicitud en ADOPCIONES
        // Usa la secuencia ADOPCIONES_SEQ si tu tabla no es identidad
        $sqlIns = "
          INSERT INTO DUCR.ADOPCIONES (ID, FECHA, USUARIO, MASCOTA)
          VALUES (DUCR.ADOPCIONES_SEQ.NEXTVAL, SYSDATE, :usuario, :mascota)
        ";
        $st = $db->prepare($sqlIns);
        $st->bindValue(':usuario', (int)$_SESSION['user_id']);
        $st->bindValue(':mascota', $mascotaId);
        $st->execute();

        // 2.2 Cambiar estado de la mascota
        $sqlUpd = "UPDATE DUCR.MASCOTAS SET ESTADO = 'En observación' WHERE ID = :id";
        $st2 = $db->prepare($sqlUpd);
        $st2->bindValue(':id', $mascotaId);
        $st2->execute();

        // Commit
        if ($db instanceof OciAdapter) { $db->commit(); }
        else if ($db instanceof PDO)   { $db->commit(); }

        $_SESSION['mascotas_success'] = '¡Solicitud enviada! El proceso de adopción ha iniciado. La mascota quedó “En observación”.';
        header('Location: mascotas_public.php'); exit();

      } catch (Throwable $e) {
        // Rollback
        if ($db instanceof OciAdapter && $db->inTransaction()) { $db->rollBack(); }
        else if ($db instanceof PDO && $db->inTransaction())   { $db->rollBack(); }

        $_SESSION['mascotas_error'] = 'No se pudo registrar la solicitud: ' . $e->getMessage();
        header('Location: adopciones.php?action=create&mascota_id=' . $mascotaId); exit();
      }
    }

    // 3) GET: solo mostrar la pantalla de confirmación
    $mascota_nombre = $mascota['nombre'] ?? 'Mascota';
    require __DIR__ . '/../views/adopciones/create.php';
  }
}
