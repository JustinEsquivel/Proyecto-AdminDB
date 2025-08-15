<?php
require_once __DIR__ . '/BaseModel.php';

class Adopcion extends BaseModel {
  public function crearSolicitud(array $d): bool {
    try {
      $this->db->beginTransaction();

      $sqlIns = "
        INSERT INTO DUCR.ADOPCIONES (ID, FECHA, USUARIO, MASCOTA)
        VALUES (DUCR.ADOPCIONES_SEQ.NEXTVAL, SYSTIMESTAMP, :usuario, :mascota)
      ";
      $st = $this->db->prepare($sqlIns);
      $st->bindValue(':usuario', (int)$d['usuario'], PDO::PARAM_INT);
      $st->bindValue(':mascota', (int)$d['mascota'], PDO::PARAM_INT);
      $st->execute();

      $sqlUpd = "
        UPDATE DUCR.MASCOTAS
           SET ESTADO = 'En Proceso'
         WHERE ID = :mascota AND ESTADO = 'Disponible'
      ";
      $su = $this->db->prepare($sqlUpd);
      $su->bindValue(':mascota', (int)$d['mascota'], PDO::PARAM_INT);
      $su->execute();

      $this->db->commit();
      return true;

    } catch (Throwable $e) {
      if ($this->db->inTransaction()) $this->db->rollBack();
      $this->error = $e->getMessage();
      return false;
    }
  }
}
