<?php
require_once __DIR__ . '/../config/db.php'; // Debe devolver PDO

abstract class BaseModel {
  /** @var PDO */
  protected $db;
  protected $error = '';

  public function __construct() {
    $this->db = Database::connect(); // Debe ser PDO
  }

  public function getError(): string {
    return $this->error;
  }
}
