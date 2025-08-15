<?php
require_once __DIR__ . '/../config/db.php'; 

abstract class BaseModel {
  /** @var PDO */
  protected $db;
  protected $error = '';

  public function __construct() {
    $this->db = Database::connect(); 
  }

  public function getError(): string {
    return $this->error;
  }
}
