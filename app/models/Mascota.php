<?php
require_once __DIR__ . '/BaseModel.php';

class Mascota extends BaseModel {

  public function all(): array {
    try {
      $sql = "
        SELECT m.ID        AS id,
               m.NOMBRE    AS nombre,
               m.RAZA      AS raza,
               m.EDAD      AS edad,
               m.DESCRIPCION AS descripcion,
               m.FOTO      AS foto,
               m.ESTADO    AS estado,
               m.USUARIO   AS usuario,
               (u.NOMBRE || ' ' || u.APELLIDO) AS propietario
          FROM DUCR.MASCOTAS m
          JOIN DUCR.USUARIOS u ON u.ID = m.USUARIO
         ORDER BY m.ID DESC
      ";
      return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
      $this->error = $e->getMessage();
      return [];
    }
  }

  public function find(int $id): ?array {
    try {
      $st = $this->db->prepare("
        SELECT ID AS id, NOMBRE AS nombre, RAZA AS raza, EDAD AS edad,
               DESCRIPCION AS descripcion, FOTO AS foto, ESTADO AS estado,
               USUARIO AS usuario
          FROM DUCR.MASCOTAS
         WHERE ID = :id
      ");
      $st->bindValue(':id', $id, PDO::PARAM_INT);
      $st->execute();
      $row = $st->fetch(PDO::FETCH_ASSOC);
      return $row ?: null;
    } catch (Throwable $e) {
      $this->error = $e->getMessage();
      return null;
    }
  }

  public function create(array $d): bool {
    try {
      $st = $this->db->prepare("
        INSERT INTO DUCR.MASCOTAS
          (ID, NOMBRE, RAZA, EDAD, DESCRIPCION, FOTO, ESTADO, USUARIO)
        VALUES
          (DUCR.MASCOTAS_SEQ.NEXTVAL, :nombre, :raza, :edad, :descripcion, :foto, :estado, :usuario)
      ");
      $st->bindValue(':nombre',      $d['nombre']);
      $st->bindValue(':raza',        $d['raza']);
      $st->bindValue(':edad',        (int)$d['edad'], PDO::PARAM_INT);
      $st->bindValue(':descripcion', $d['descripcion']);
      $st->bindValue(':foto',        $d['foto']);
      $st->bindValue(':estado',      $d['estado']);
      $st->bindValue(':usuario',     (int)$d['usuario'], PDO::PARAM_INT);
      return $st->execute();
    } catch (Throwable $e) {
      $this->error = $e->getMessage();
      return false;
    }
  }

  public function update(int $id, array $d): bool {
    try {
      $st = $this->db->prepare("
        UPDATE DUCR.MASCOTAS
           SET NOMBRE = :nombre,
               RAZA = :raza,
               EDAD = :edad,
               DESCRIPCION = :descripcion,
               FOTO = :foto,
               ESTADO = :estado,
               USUARIO = :usuario
         WHERE ID = :id
      ");
      $st->bindValue(':nombre',      $d['nombre']);
      $st->bindValue(':raza',        $d['raza']);
      $st->bindValue(':edad',        (int)$d['edad'], PDO::PARAM_INT);
      $st->bindValue(':descripcion', $d['descripcion']);
      $st->bindValue(':foto',        $d['foto']);
      $st->bindValue(':estado',      $d['estado']);
      $st->bindValue(':usuario',     (int)$d['usuario'], PDO::PARAM_INT);
      $st->bindValue(':id',          $id, PDO::PARAM_INT);
      return $st->execute();
    } catch (Throwable $e) {
      $this->error = $e->getMessage();
      return false;
    }
  }

  public function delete(int $id): bool {
    try {
      $st = $this->db->prepare("DELETE FROM DUCR.MASCOTAS WHERE ID = :id");
      $st->bindValue(':id', $id, PDO::PARAM_INT);
      return $st->execute();
    } catch (Throwable $e) {
      $this->error = $e->getMessage();
      return false;
    }
  }

  public function disponibles(int $limit = 50): array {
    try {
      // Subconsulta + ROWNUM para mantener ORDER BY y limitar
      $sql = "
        SELECT *
          FROM (
            SELECT ID AS id, NOMBRE AS nombre, RAZA AS raza,
                   EDAD AS edad, FOTO AS foto, ESTADO AS estado
              FROM DUCR.MASCOTAS
             WHERE ESTADO = 'Disponible'
             ORDER BY ID DESC
          )
         WHERE ROWNUM <= :lim
      ";
      $st = $this->db->prepare($sql);
      $st->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
      $st->execute();
      return $st->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
      $this->error = $e->getMessage();
      return [];
    }
  }
}
