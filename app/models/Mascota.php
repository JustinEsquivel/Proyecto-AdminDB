<?php
require_once __DIR__ . '/BaseModel.php';

class Mascota extends BaseModel {

  public function all(): array {
    try {
      $sql = "
        SELECT
          m.ID        AS id,
          m.NOMBRE    AS nombre,
          m.RAZA      AS raza,
          m.EDAD      AS edad,
          m.DESCRIPCION AS descripcion,
          m.FOTO      AS foto,
          m.ESTADO    AS estado,
          m.USUARIO   AS usuario,
          (NVL(u.NOMBRE,'') || ' ' || NVL(u.APELLIDO,'')) AS propietario
        FROM DUCR.MASCOTAS m
        LEFT JOIN DUCR.USUARIOS u ON u.ID = m.USUARIO
        ORDER BY m.ID DESC
      ";
      if ($this->db instanceof OciAdapter) {
        $res = $this->db->query($sql);
        return $res ? $res->fetch_all() : [];
      } else { 
        $st = $this->db->query($sql);
        return $st ? $st->fetchAll(PDO::FETCH_ASSOC) : [];
      }
    } catch (Throwable $e) {
      $this->error = $e->getMessage();
      return [];
    }
  }

  public function find(int $id): ?array {
    try {
      $sql = "
        SELECT
          ID, NOMBRE, RAZA, EDAD, DESCRIPCION, FOTO, ESTADO, USUARIO
        FROM DUCR.MASCOTAS
        WHERE ID = :id
        FETCH FIRST 1 ROWS ONLY
      ";
      $st = $this->db->prepare($sql);
      $st->bindValue(':id', $id);
      $st->execute();

      if ($this->db instanceof OciAdapter) {
        $row = $st->fetch();
      } else { 
        $row = $st->fetch(PDO::FETCH_ASSOC);
      }
      return $row ?: null;
    } catch (Throwable $e) {
      $this->error = $e->getMessage();
      return null;
    }
  }

  public function findById(int $id) {
    return $this->find($id);
  }

  public function create(array $d): bool {
    try {
      $sql = "
        INSERT INTO DUCR.MASCOTAS
          (ID, NOMBRE, RAZA, EDAD, DESCRIPCION, FOTO, ESTADO, USUARIO)
        VALUES
          (DUCR.MASCOTAS_SEQ.NEXTVAL, :nombre, :raza, :edad, :descripcion, :foto, :estado, :usuario)
      ";
      $st = $this->db->prepare($sql);
      $st->bindValue(':nombre',      $d['nombre']);
      $st->bindValue(':raza',        $d['raza']);
      $st->bindValue(':edad',        (int)$d['edad']);
      $st->bindValue(':descripcion', $d['descripcion']);
      $st->bindValue(':foto',        $d['foto']);
      $st->bindValue(':estado',      $d['estado']);
      $st->bindValue(':usuario',     (int)$d['usuario']);
      return $st->execute();
    } catch (Throwable $e) {
      $this->error = $e->getMessage();
      return false;
    }
  }

  public function update(int $id, array $d): bool {
    try {
      $sql = "
        UPDATE DUCR.MASCOTAS
           SET NOMBRE = :nombre,
               RAZA = :raza,
               EDAD = :edad,
               DESCRIPCION = :descripcion,
               FOTO = :foto,
               ESTADO = :estado,
               USUARIO = :usuario
         WHERE ID = :id
      ";
      $st = $this->db->prepare($sql);
      $st->bindValue(':nombre',      $d['nombre']);
      $st->bindValue(':raza',        $d['raza']);
      $st->bindValue(':edad',        (int)$d['edad']);
      $st->bindValue(':descripcion', $d['descripcion']);
      $st->bindValue(':foto',        $d['foto']);
      $st->bindValue(':estado',      $d['estado']);
      $st->bindValue(':usuario',     (int)$d['usuario']);
      $st->bindValue(':id',          $id);
      return $st->execute();
    } catch (Throwable $e) {
      $this->error = $e->getMessage();
      return false;
    }
  }

  public function delete(int $id): bool 
  {
    try {
      if ($this->db instanceof OciAdapter) {
        $this->db->beginTransaction();
      }

      // 1) hijos
      $st = $this->db->prepare("DELETE FROM DUCR.ADOPCIONES WHERE MASCOTA = :id");
      $st->bindValue(':id', $id); $st->execute();

      $st = $this->db->prepare("DELETE FROM DUCR.HISTORIALMEDICO WHERE MASCOTA = :id");
      $st->bindValue(':id', $id); $st->execute();

      $st = $this->db->prepare("DELETE FROM DUCR.REPORTES WHERE MASCOTA = :id");
      $st->bindValue(':id', $id); $st->execute();

      // 2) padre
      $st = $this->db->prepare("DELETE FROM DUCR.MASCOTAS WHERE ID = :id");
      $st->bindValue(':id', $id);
      $ok = $st->execute();

      if ($this->db instanceof OciAdapter) {
        $ok ? $this->db->commit() : $this->db->rollBack();
      }
      return $ok;
    } catch (Throwable $e) {
      if ($this->db instanceof OciAdapter && $this->db->inTransaction()) {
        $this->db->rollBack();
      }
      $this->error = $e->getMessage();
      return false;
    }
  }

  public function disponibles(int $limit = 50): array {
    try {
      $sql = "
        SELECT ID, NOMBRE, RAZA, EDAD, FOTO, ESTADO
        FROM (
          SELECT ID, NOMBRE, RAZA, EDAD, FOTO, ESTADO
          FROM DUCR.MASCOTAS
          WHERE UPPER(ESTADO) = 'DISPONIBLE'
          ORDER BY ID DESC
        )
        WHERE ROWNUM <= :lim
      ";
      $st = $this->db->prepare($sql);
      $st->bindValue(':lim', (int)$limit);
      $st->execute();

      if ($this->db instanceof OciAdapter) {
        return $st->fetchAll();
      } else { 
        return $st->fetchAll(PDO::FETCH_ASSOC);
      }
    } catch (Throwable $e) {
      $this->error = $e->getMessage();
      return [];
    }
  }
}
