<?php
class Database {
    public static function connect(): PDO {
        // === Parámetros de conexión Oracle ===
        $host         = '127.0.0.1';   
        $port         = 1521;          
        $service_name = 'orcl';         
        $user         = 'PATITAS';    
        $pass         = 'patitas123';

        // DSN con Easy Connect + charset UTF-8 de Oracle
        $dsn = "oci:dbname=//$host:$port/$service_name;charset=AL32UTF8";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,      
            PDO::ATTR_CASE               => PDO::CASE_NATURAL,     
        ]);
        return $pdo;
    }
}
