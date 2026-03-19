<?php
namespace SGEI\Config;

use PDO;
use PDOException;

class Database {
    // En PHP 8.1+ es mejor definir el tipo (string) para evitar avisos
    private string $host = 'localhost';
    private string $db   = 'sgei';
    private string $user = 'root';
    private string $pass = '';

    /**
     * Establece la conexión a la base de datos
     * @return PDO
     */
    public function connect(): PDO {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db};charset=utf8mb4";
            
            $options = [
                // PHP 8.1 ya trae ERRMODE_EXCEPTION por defecto, pero lo dejamos por seguridad
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // Desactivamos emulación para usar tipos de datos reales (int como int, no string)
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            return new PDO($dsn, $this->user, $this->pass, $options);

        } catch (PDOException $e) {
            // En producción podrías cambiar el die por un log, 
            // pero para tu proyecto escolar esto es perfecto para debugear.
            die("Error de conexión técnica (SGEI): " . $e->getMessage());
        }
    }
}