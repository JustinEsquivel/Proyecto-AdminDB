<?php
require_once __DIR__ . '/BaseModel.php';

class Usuario extends BaseModel {

  public function findByEmail(string $email) {
    try {
      $sql = "
        SELECT u.ID        AS id,
               u.NOMBRE    AS nombre,
               u.APELLIDO  AS apellido,
               u.EMAIL     AS email,
               u.PASSWORD  AS password,
               u.TELEFONO  AS telefono,
               u.ROL       AS rol,
               r.NOMBRE    AS rol_nombre
          FROM DUCR.USUARIOS u
          LEFT JOIN DUCR.ROLES r ON r.ID = u.ROL
         WHERE LOWER(u.EMAIL) = LOWER(:email)
         FETCH FIRST 1 ROWS ONLY
      ";
      $st = $this->db->prepare($sql);
      $st->bindValue(':email', $email);
      $st->execute();
      return $st->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (Throwable $e) {
      $this->error = $e->getMessage();
      return null;
    }
  }

  public function emailExists(string $email): bool {
    try {
      $st = $this->db->prepare("
        SELECT 1
          FROM DUCR.USUARIOS
         WHERE LOWER(EMAIL) = LOWER(:email)
         FETCH FIRST 1 ROWS ONLY
      ");
      $st->bindValue(':email', $email);
      $st->execute();
      return (bool)$st->fetchColumn();
    } catch (Throwable $e) {
      $this->error = $e->getMessage();
      return false;
    }
  }

  public function create(array $d): bool {
    try {
      $st = $this->db->prepare("
        INSERT INTO DUCR.USUARIOS
          (ID, NOMBRE, APELLIDO, EMAIL, PASSWORD, TELEFONO, ROL)
        VALUES
          (DUCR.USUARIOS_SEQ.NEXTVAL, :nombre, :apellido, :email, :password, :telefono, :rol)
      ");
      $st->bindValue(':nombre',   $d['nombre']);
      $st->bindValue(':apellido', $d['apellido']);
      $st->bindValue(':email',    $d['email']);
      $st->bindValue(':password', $d['password']); // hash bcrypt
      $st->bindValue(':telefono', $d['telefono']);
      $st->bindValue(':rol',      (int)$d['rol'], PDO::PARAM_INT);

      return $st->execute();
    } catch (Throwable $e) {
      $this->error = $e->getMessage();
      return false;
    }
  }
}
