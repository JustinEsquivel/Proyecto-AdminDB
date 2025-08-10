<?php
require_once __DIR__ . '/BaseModel.php';

class Usuario extends BaseModel {

  public function findByEmail(string $email) {
    $sql = "SELECT * FROM usuarios WHERE LOWER(email) = LOWER(:email)";
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':email', $email);
    $stmt->execute();
    $row = $stmt->fetch();
    return $row ?: null;
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
      error_log("ERROR Oracle (emailExists): " . $e->getMessage());
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
        $st->bindValue(':email',    strtolower($d['email'])); // normaliza a minúsculas
        $st->bindValue(':password', $d['password']); // ya debe venir hasheada
        $st->bindValue(':telefono', $d['telefono']);
        $st->bindValue(':rol',      (int)$d['rol'], PDO::PARAM_INT);

        return $st->execute();
    } catch (Throwable $e) {
        $this->error = $e->getMessage();
        return false;
    }
  }




  public function getError(): string {
    return isset($this->error) && $this->error !== '' 
        ? $this->error 
        : 'Error desconocido en la base de datos.';
}

}
