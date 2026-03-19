<?php
session_start();
/** @var \SGEI\Config\Database $db */
require_once '../../config/Database.php';

// 1. SEGURIDAD DE SESIÓN
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

$database = new \SGEI\Config\Database();
$db = $database->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();

        // 2. SANITIZACIÓN (PHP 8.1: Aseguramos que siempre sean strings antes de limpiar)
        $nombre_raw = $_POST['nombre'] ?? '';
        $correo_raw = $_POST['correo'] ?? '';
        $fecha_raw  = $_POST['fecha_nac'] ?? '';

        $nombre    = htmlspecialchars(strip_tags(trim((string)$nombre_raw)));
        $correo    = filter_var(trim((string)$correo_raw), FILTER_SANITIZE_EMAIL);
        $fecha_nac = (string)$fecha_raw; 

        // --- 3. GENERACIÓN SEGURA DEL NÚMERO DE CONTROL ---
        $prefijo = "100000000000"; 
        $stmtCheck = $db->prepare("SELECT numero_control FROM alumnos WHERE numero_control LIKE ? ORDER BY numero_control DESC LIMIT 1");
        $stmtCheck->execute([$prefijo . "%"]);
        $ultimo = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($ultimo) {
            // PHP 8.1 es estricto con substr(): aseguramos que sea string
            $last_control = (string)$ultimo['numero_control'];
            $secuencia = (int)substr($last_control, -4);
            $nueva_secuencia = str_pad((string)($secuencia + 1), 4, "0", STR_PAD_LEFT);
        } else {
            $nueva_secuencia = "0001";
        }
        
        $n_control_final = $prefijo . $nueva_secuencia;

        // --- 4. HASHING (Estándar BCRYPT para seguridad 2026)
        $pass_segura = password_hash($n_control_final, PASSWORD_BCRYPT);
        
        // --- 5. REGISTRO DE USUARIO ---
        $sqlUser = "INSERT INTO usuarios (nombre_completo, correo, password, rol) VALUES (?, ?, ?, 'alumno')";
        $stmtUser = $db->prepare($sqlUser);
        $stmtUser->execute([$nombre, $correo, $pass_segura]);
        
        $idUsuario = $db->lastInsertId();

        // --- 6. REGISTRO DE ALUMNO ---
        $sqlAlu = "INSERT INTO alumnos (id_alumno, numero_control, fecha_nacimiento) VALUES (?, ?, ?)";
        $stmtAlu = $db->prepare($sqlAlu);
        $stmtAlu->execute([$idUsuario, $n_control_final, $fecha_nac]);

        $db->commit();
        
        header("Location: control_escolar.php?msj=registrado&control=" . urlencode($n_control_final));
        exit();

    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        // Registramos el error en el log de XAMPP para que lo revises si algo falla
        error_log("Error en SGEI Registro: " . $e->getMessage()); 
        header("Location: control_escolar.php?msj=error");
        exit();
    }
}