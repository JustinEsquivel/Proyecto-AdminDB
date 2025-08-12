<?php
require_once __DIR__ . '/BaseModel.php';

class Eventos extends BaseModel {

  /** Lista completa con propietario */
  public function all(): array {
    try {
      $sql = "
        SELECT
          e.ID        AS id,
          e.NOMBRE    AS nombre,
          e.TIPO    AS tipo,
          e.UBICACION AS ubicacion,
          e.DESCRIPCION AS descripcion,
          e.FECHA AS fecha,
          e.ESTADO    AS estado,
          e.USUARIO   AS usuario,
          (NVL(u.NOMBRE,'') || ' ' || NVL(u.APELLIDO,'')) AS propietario
        FROM DUCR.EVENTOS e
        LEFT JOIN DUCR.USUARIOS u ON u.ID = e.USUARIO
        ORDER BY e.ID DESC
      ";
      if ($this->db instanceof OciAdapter) {
        $res = $this->db->query($sql);
        return $res ? $res->fetch_all() : [];
      } else { // PDO
        $st = $this->db->query($sql);
        return $st ? $st->fetchAll(PDO::FETCH_ASSOC) : [];
      }
    } catch (Throwable $e) {
      $this->error = $e->getMessage();
      return [];
    }
  }

  /** Buscar por ID (retorna array o null) */
  public function find(int $id): ?array {
    try {
      $sql = "
        SELECT
          ID, NOMBRE, TIPO, UBICACION, DESCRIPCION, FECHA, ESTADO, USUARIO
        FROM DUCR.EVENTOS
        WHERE ID = :id
        FETCH FIRST 1 ROWS ONLY
      ";
      $st = $this->db->prepare($sql);
      $st->bindValue(':id', $id);
      $st->execute();

      if ($this->db instanceof OciAdapter) {
        $row = $st->fetch();
      } else { // PDO
        $row = $st->fetch(PDO::FETCH_ASSOC);
      }
      return $row ?: null;
    } catch (Throwable $e) {
      $this->error = $e->getMessage();
      return null;
    }
  }

  /** Alias por compatibilidad con tu controlador */
  public function findById(int $id) {
    return $this->find($id);
  }

  /** Crear (si más adelante insertas), aquí asumo que tienes una secuencia para MASCOTAS */
  public function create(array $d): bool {
    try {
      // Si tu tabla EVENTOS no tiene identidad, crea una secuencia DUCR.EVENTOS_SEQ y úsala aquí:
      $sql = "
        INSERT INTO DUCR.EVENTOS
          (ID, NOMBRE, TIPO, UBICACION, DESCRIPCION, FECHA, ESTADO, USUARIO)
        VALUES
          (DUCR.EVENTOS_SEQ.NEXTVAL, :nombre, :tipo, :ubicacion, :descripcion, :fecha, :estado, :usuario)
      ";
      $st = $this->db->prepare($sql);
      $st->bindValue(':nombre',      $d['nombre']);
      $st->bindValue(':tipo',        $d['tipo']);
      $st->bindValue(':ubicacion',        $d['ubicacion']);
      $st->bindValue(':descripcion', $d['descripcion']);
      $st->bindValue(':fecha',        $d['fecha']);
      $st->bindValue(':estado',      $d['estado']);
      $st->bindValue(':usuario',     (int)$d['usuario']);
      return $st->execute();
    } catch (Throwable $e) {
      $this->error = $e->getMessage();
      return false;
    }
  }

  /** Actualizar */
  public function update(int $id, array $d): bool {
    try {
      $sql = "
        UPDATE DUCR.EVENTOS
           SET NOMBRE = :nombre,
               TIPO = :tipo,
               UBICACION = :ubicacion,
               DESCRIPCION = :descripcion,
               FECHA = :fecha,
               ESTADO = :estado,
               USUARIO = :usuario
         WHERE ID = :id
      ";
      $st = $this->db->prepare($sql);
      $st->bindValue(':nombre',      $d['nombre']);
      $st->bindValue(':tipo',        $d['tipo']);
      $st->bindValue(':ubicacion',        $d['ubicacion']);
      $st->bindValue(':descripcion', $d['descripcion']);
      $st->bindValue(':fecha',        $d['fecha']);
      $st->bindValue(':estado',      $d['estado']);
      $st->bindValue(':usuario',     (int)$d['usuario']);
      $st->bindValue(':id',          $id);
      return $st->execute();
    } catch (Throwable $e) {
      $this->error = $e->getMessage();
      return false;
    }
  }

  /** Eliminar */
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

  /** Disponibles (TOP n) – compatible con Oracle: ordena y luego limita */
  public function disponibles(int $limit = 50): array {
    try {
      // Usamos subconsulta + ROWNUM para mantener el ORDER BY
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
      } else { // PDO
        return $st->fetchAll(PDO::FETCH_ASSOC);
      }
    } catch (Throwable $e) {
      $this->error = $e->getMessage();
      return [];
    }
  }
}
