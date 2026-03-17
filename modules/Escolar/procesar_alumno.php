<?php
session_start();
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

        // 2. SANITIZACIÓN
        $nombre = htmlspecialchars(strip_tags(trim($_POST['nombre'])));
        $correo = filter_var(trim($_POST['correo']), FILTER_SANITIZE_EMAIL);
        $fecha_nac = $_POST['fecha_nac']; // Este viene del name del input

        // --- 3. GENERACIÓN SEGURA DEL NÚMERO DE CONTROL ---
        $prefijo = "100000000000"; 
        $stmtCheck = $db->prepare("SELECT numero_control FROM alumnos WHERE numero_control LIKE ? ORDER BY numero_control DESC LIMIT 1");
        $stmtCheck->execute([$prefijo . "%"]);
        $ultimo = $stmtCheck->fetch();

        if ($ultimo) {
            $secuencia = (int)substr($ultimo['numero_control'], -4);
            $nueva_secuencia = str_pad($secuencia + 1, 4, "0", STR_PAD_LEFT);
        } else {
            $nueva_secuencia = "0001";
        }
        
        $n_control_final = $prefijo . $nueva_secuencia;

        // --- 4. HASHING
        $pass_segura = password_hash($n_control_final, PASSWORD_BCRYPT);
        
        // --- 5. REGISTRO DE USUARIO ---
        $sqlUser = "INSERT INTO usuarios (nombre_completo, correo, password, rol) VALUES (?, ?, ?, 'alumno')";
        $stmtUser = $db->prepare($sqlUser);
        $stmtUser->execute([$nombre, $correo, $pass_segura]);
        
        $idUsuario = $db->lastInsertId();

        // --- 6. REGISTRO DE ALUMNO (CORREGIDO) ---
        // Cambiamos 'fecha_de_nacimiento' por 'fecha_nacimiento'
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
        // Si quieres ver el error real si vuelve a fallar, descomenta la siguiente línea:
        // die("Error fatal: " . $e->getMessage()); 
        error_log($e->getMessage()); 
        header("Location: control_escolar.php?msj=error");
        exit();
    }
}