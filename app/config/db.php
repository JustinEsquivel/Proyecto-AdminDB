<?php

class Database {
  public static function connect(): OciAdapter {
    $host    = '127.0.0.1';   // cambia si tu Oracle está en otro host
    $port    = 1521;          // puerto Oracle
    $service = 'orcl';          // SERVICE_NAME (p. ej. XE, XEPDB1)
    $user    = 'DUCR';        // esquema/usuario
    $pass    = '123';

    // Cadena de conexión con SERVICE_NAME
    $connStr = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=$host)(PORT=$port))(CONNECT_DATA=(SERVICE_NAME=$service)))";

    $conn = @oci_connect($user, $pass, $connStr, 'AL32UTF8');
    if (!$conn) {
      $e = oci_error();
      die('Error de conexión Oracle: ' . ($e['message'] ?? 'desconocido'));
    }
    return new OciAdapter($conn);
  }
}

class OciAdapter {
  /** @var resource */
  private $conn;
  /** @var bool */
  private $inTx = false;

  public function __construct($conn) { $this->conn = $conn; }

  /** Consulta directa (sin binds). Devuelve OciResult con rows ya bufferizados. */
  public function query(string $sql): OciResult|false {
    $stid = @oci_parse($this->conn, $sql);
    if (!$stid) return false;
    $mode = $this->inTx ? OCI_NO_AUTO_COMMIT : OCI_COMMIT_ON_SUCCESS;
    if (!@oci_execute($stid, $mode)) return false;

    $rows = [];
    // Para SELECT recogemos filas; para DML devolvemos result vacío
    if ($this->isSelect($sql)) {
      while ($r = oci_fetch_assoc($stid)) {
        // normaliza claves a minúsculas para que vistas sigan usando $row['nombre']
        $rows[] = array_change_key_case($r, CASE_LOWER);
      }
    }
    // Libera el statement real y devolvemos un resultado "bufferizado"
    oci_free_statement($stid);
    return new OciResult($rows);
  }

  /** Preparar statement con placeholders nombrados (:id, :nombre, ...) */
  public function prepare(string $sql): OciStatement|false {
    $stid = @oci_parse($this->conn, $sql);
    if (!$stid) return false;
    return new OciStatement($this->conn, $stid, $this);
  }

  public function beginTransaction(): bool {
    $this->inTx = true;
    return true;
  }

  public function commit(): bool {
    $ok = @oci_commit($this->conn);
    $this->inTx = false;
    return $ok;
  }

  public function rollBack(): bool {
    $ok = @oci_rollback($this->conn);
    $this->inTx = false;
    return $ok;
  }

  public function inTransaction(): bool { return $this->inTx; }

  /** Utilidad: detectar si parece SELECT (muy simple) */
  private function isSelect(string $sql): bool {
    return preg_match('/^\s*SELECT\b/i', $sql) === 1;
  }
}

class OciStatement {
  /** @var resource */
  private $conn;
  /** @var resource */
  private $stid;
  /** @var OciAdapter */
  private $adapter;
  /** @var array<string,mixed> */
  private $binds = [];

  public function __construct($conn, $stid, OciAdapter $adapter) {
    $this->conn = $conn;
    $this->stid = $stid;
    $this->adapter = $adapter;
  }

  /** Bind de valor por nombre, ej: bindValue(':id', 10) */
  public function bindValue(string $name, $value, int $type = null): bool {
    // Asegura que el placeholder empiece con :
    if ($name[0] !== ':') $name = ':' . $name;
    // Para CLOB grandes puedes usar OCI_B_CLOB; aquí manejamos VARCHAR/NUMBER genéricos
    $this->binds[$name] = $value;
    return true;
  }

  /** Ejecuta; devuelve true/false. */
  public function execute(array $params = []): bool {
    foreach ($params as $k => $v) {
      $key = ($k[0] === ':') ? $k : ':' . $k;
      $this->binds[$key] = $v;
    }

    if (!isset($this->ociVars) || !is_array($this->ociVars)) {
    $this->ociVars = [];
    }
    foreach ($this->binds as $k => $v) {
    // crear una variable única por bind y guardarla para mantener la referencia viva
    $this->ociVars[$k] = $v;
    if (!@oci_bind_by_name($this->stid, $k, $this->ociVars[$k], -1)) {
        $err = oci_error($this->stid);
        throw new RuntimeException("Error en bind $k: " . ($err['message'] ?? 'desconocido'));
    }
    }

    $mode = $this->adapter->inTransaction() ? OCI_NO_AUTO_COMMIT : OCI_COMMIT_ON_SUCCESS;
    if (!@oci_execute($this->stid, $mode)) {
      $err = oci_error($this->stid);
      throw new RuntimeException($err['message'] ?? 'Error desconocido en Oracle');
    }

    return true;
    }


  /** Primera fila (assoc) */
  public function fetch(): array|null {
    $r = oci_fetch_assoc($this->stid);
    return $r ? array_change_key_case($r, CASE_LOWER) : null;
  }

  /** Todas las filas (assoc) */
  public function fetchAll(): array {
    $rows = [];
    while ($r = oci_fetch_assoc($this->stid)) {
      $rows[] = array_change_key_case($r, CASE_LOWER);
    }
    return $rows;
  }

  /** Compat: para código que espera ->get_result()->fetch_assoc() (mysqli-style) */
  public function get_result(): OciResult {
    return new OciResult($this->fetchAll());
  }

  public function free(): void {
    if ($this->stid) { @oci_free_statement($this->stid); $this->stid = null; }
  }

  public function __destruct() { $this->free(); }
}

class OciResult implements IteratorAggregate {
  private array $rows;
  private int $pos = 0;
  public int $num_rows = 0;

  public function __construct(array $rows) {
    $this->rows = $rows;
    $this->num_rows = count($rows);
  }

  /** Siguiente fila estilo mysqli */
  public function fetch_assoc(): array|null {
    if ($this->pos >= $this->num_rows) return null;
    return $this->rows[$this->pos++];
  }

  /** Todas las filas estilo mysqli */
  public function fetch_all($mode = null): array { return $this->rows; }

  /** Permite foreach directo sobre el resultado */
  public function getIterator(): Traversable {
    foreach ($this->rows as $r) yield $r;
  }
}
