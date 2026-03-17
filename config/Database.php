<?php
namespace SGEI\Config;

use PDO;
use PDOException;

class Database {
    // Quitamos 'string' para máxima compatibilidad
    private $host = 'localhost';
    private $db   = 'sgei';
    private $user = 'root';
    private $pass = '';

    public function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            return new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            die("Error de conexión técnica: " . $e->getMessage());
        }
    }
}