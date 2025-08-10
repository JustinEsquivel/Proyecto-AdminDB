<?php
require_once __DIR__ . '/app/config/auth.php';
require_once __DIR__ . '/app/config/db.php';

start_session_safe();
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$db = Database::connect(); // Puede ser PDO o OciAdapter

// ==== Helpers agnósticos (PDO u OciAdapter) ====
function db_count($db, string $sql): int {
  try {
    if ($db instanceof OciAdapter) {
      $res = $db->query($sql);
      if (!$res) return 0;
      $row = $res->fetch_assoc();
      return (int)($row['c'] ?? 0);
    } else { // PDO
      $st = $db->query($sql);
      $row = $st ? $st->fetch(PDO::FETCH_ASSOC) : null;
      return (int)($row['c'] ?? 0);
    }
  } catch (Throwable $e) {
    return 0;
  }
}

function db_fetch_all($db, string $sql): array {
  try {
    if ($db instanceof OciAdapter) {
      $res = $db->query($sql);
      return $res ? $res->fetch_all() : [];
    } else { // PDO
      $st = $db->query($sql);
      return $st ? $st->fetchAll(PDO::FETCH_ASSOC) : [];
    }
  } catch (Throwable $e) {
    return [];
  }
}

// Totales
$totMascotas    = db_count($db, "SELECT COUNT(*) c FROM DUCR.MASCOTAS");
$totDisponibles = db_count($db, "SELECT COUNT(*) c FROM DUCR.MASCOTAS WHERE ESTADO = 'Disponible'");
$totAdoptadas   = db_count($db, "SELECT COUNT(*) c FROM DUCR.MASCOTAS WHERE ESTADO = 'Adoptado'");
$totAdopciones  = db_count($db, "SELECT COUNT(*) c FROM DUCR.ADOPCIONES");
$totReportes    = db_count($db, "SELECT COUNT(*) c FROM DUCR.REPORTES");

// Ojo con la Ñ: si la tabla fue creada entre comillas con Ñ, hay que citarla igual
try {
  $totCampanias = db_count($db, 'SELECT COUNT(*) c FROM DUCR."CAMPAÑAS"');
} catch (Throwable $e) {
  $totCampanias = 0;
}

// Mascotas recientes (TOP 5)
$mascotasRec = db_fetch_all($db, "
  SELECT ID AS id, NOMBRE AS nombre, RAZA AS raza, ESTADO AS estado
    FROM DUCR.MASCOTAS
   ORDER BY ID DESC
   FETCH FIRST 5 ROWS ONLY
");

// Adopciones recientes (TOP 5)
$adopRec = db_fetch_all($db, "
  SELECT A.ID AS id,
         TO_CHAR(A.FECHA, 'YYYY-MM-DD HH24:MI:SS') AS fecha,
         U.NOMBRE AS usuario,
         M.NOMBRE AS mascota
    FROM DUCR.ADOPCIONES A
    JOIN DUCR.USUARIOS   U ON U.ID = A.USUARIO
    JOIN DUCR.MASCOTAS   M ON M.ID = A.MASCOTA
   ORDER BY A.ID DESC
   FETCH FIRST 5 ROWS ONLY
");

$totObservacion = db_count(
  $db,
  "SELECT COUNT(*) c
     FROM DUCR.MASCOTAS
    WHERE UPPER(ESTADO) IN ('EN OBSERVACION','EN OBSERVACIÓN')"
);

$totTratamiento = db_count(
  $db,
  "SELECT COUNT(*) c
     FROM DUCR.MASCOTAS
    WHERE UPPER(ESTADO) = 'EN TRATAMIENTO'"
);


include __DIR__ . '/app/views/partials/header.php';
?>
<div class="container py-4">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
      <h2 class="mb-1">Panel de <?= htmlspecialchars($_SESSION['nombre'] ?? '') ?></h2>
      <p class="text-muted mb-0">Resumen y accesos de gestión.</p>
    </div>
  </div>

  <div class="row text-center g-3 mb-4">
    <div class="col-md-3 mb-3">
      <div class="card shadow-sm border-0 h-100"><div class="card-body">
        <div class="display-4 mb-1"><?= $totMascotas ?></div>
        <div class="text-muted">Total de Mascotas</div>
      </div></div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card shadow-sm border-0 h-100"><div class="card-body">
        <div class="display-4 mb-1"><?= $totDisponibles ?></div>
        <div class="text-muted">Mascotas Disponibles</div>
      </div></div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card shadow-sm border-0 h-100"><div class="card-body">
        <div class="display-4 mb-1"><?= $totAdoptadas ?></div>
        <div class="text-muted">Mascotas Adoptadas</div>
      </div></div>
    </div>
    <div class="col-md-3 mb-3">
    <div class="card shadow-sm border-0 h-100"><div class="card-body">
      <div class="display-4 mb-1"><?= $totObservacion ?></div>
      <div class="text-muted">En observación</div>
    </div></div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card shadow-sm border-0 h-100"><div class="card-body">
        <div class="display-4 mb-1"><?= $totTratamiento ?></div>
        <div class="text-muted">En tratamiento</div>
      </div></div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card shadow-sm border-0 h-100"><div class="card-body">
        <div class="display-4 mb-1"><?= $totAdopciones ?></div>
        <div class="text-muted">Solicitudes de adopciones</div>
      </div></div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card shadow-sm border-0 h-100"><div class="card-body">
        <div class="display-4 mb-1"><?= $totCampanias ?></div>
        <div class="text-muted">Campañas</div>
      </div></div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card shadow-sm border-0 h-100"><div class="card-body">
        <div class="display-4 mb-1"><?= $totReportes ?></div>
        <div class="text-muted">Reportes</div>
      </div></div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-6 mb-4">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-header bg-white border-0">
          <strong><i class="fas fa-paw"></i> Mascotas recientes</strong>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table mb-0">
              <thead class="thead-light">
                <tr><th>Nombre</th><th>Raza</th><th>Estado</th></tr>
              </thead>
              <tbody>
                <?php if (!empty($mascotasRec)): foreach($mascotasRec as $r): ?>
                  <tr>
                    <td><?= htmlspecialchars($r['nombre'] ?? '') ?></td>
                    <td><?= htmlspecialchars($r['raza'] ?? '') ?></td>
                    <td>
                      <?php $estado = $r['estado'] ?? 'Disponible'; ?>
                      <span class="badge <?= $estado==='Disponible' ? 'badge-success' : ($estado==='En Proceso'?'badge-warning':'badge-secondary') ?>">
                        <?= htmlspecialchars($estado) ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; else: ?>
                  <tr><td colspan="3" class="text-center text-muted">Sin registros aún</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
        <div class="card-footer bg-white border-0 text-right">
          <a href="mascotas.php" class="btn btn-sm btn-primary">Ir a Mascotas</a>
        </div>
      </div>
    </div>

    <div class="col-lg-6 mb-4">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-header bg-white border-0">
          <strong><i class="fas fa-heart"></i> Solicitudes de adopciones recientes</strong>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table mb-0">
              <thead class="thead-light">
                <tr><th>Fecha</th><th>Usuario</th><th>Mascota</th></tr>
              </thead>
              <tbody>
                <?php if (!empty($adopRec)): foreach($adopRec as $a): ?>
                  <tr>
                    <?php
                      $fecha = $a['fecha'] ?? null;
                      $fecha_fmt = $fecha ? date('d/m/Y H:i', strtotime($fecha)) : '';
                    ?>
                    <td><?= htmlspecialchars($fecha_fmt) ?></td>
                    <td><?= htmlspecialchars($a['usuario'] ?? '') ?></td>
                    <td><?= htmlspecialchars($a['mascota'] ?? '') ?></td>
                  </tr>
                <?php endforeach; else: ?>
                  <tr><td colspan="3" class="text-center text-muted">Sin registros aún</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
<?php include __DIR__ . '/app/views/partials/footer.php'; ?>
