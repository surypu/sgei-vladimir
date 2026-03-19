<?php
session_start();
/** @var \SGEI\Config\Database $db */
require_once '../../config/Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // PHP 8.1 es estricto: Aseguramos que los datos sean strings y no nulls
    $correo_raw = $_POST['correo'] ?? '';
    $pass_raw = $_POST['password'] ?? '';

    $correo = filter_var(trim((string)$correo_raw), FILTER_SANITIZE_EMAIL);
    $pass = trim((string)$pass_raw); // Número de control del alumno

    // 1. --- VALIDACIÓN PARA EL ADMIN (Hardcoded para que nunca pierdas acceso) ---
    if ($correo === 'admin@bladimir.edu' && $pass === '12345') {
        session_regenerate_id(true); // Seguridad extra en PHP 8.1
        $_SESSION['id'] = 1;
        $_SESSION['nombre'] = "Admin Bladimir";
        $_SESSION['rol'] = 'admin';
        header("Location: ../Escolar/control_escolar.php"); 
        exit();
    } 

    // 2. --- VALIDACIÓN PARA ALUMNOS ---
    try {
        $database = new \SGEI\Config\Database();
        $db = $database->connect();

        // Buscamos al usuario por su correo
        $stmt = $db->prepare("SELECT id_usuario, nombre_completo, rol, password FROM usuarios WHERE correo = ?");
        $stmt->execute([$correo]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // PHP 8.1: Validamos que $user no sea false antes de usar password_verify
        if ($user && password_verify($pass, (string)$user['password'])) {
            
            // Seguridad: Cambiamos el ID de sesión al entrar
            session_regenerate_id(true);

            $_SESSION['id'] = $user['id_usuario'];
            $_SESSION['nombre'] = $user['nombre_completo'];
            $_SESSION['rol'] = $user['rol'];

            // Redirección según el rol
            if ($user['rol'] === 'admin') {
                header("Location: ../Escolar/control_escolar.php");
            } else {
                header("Location: ../academico/mi_perfil.php");
            }
            exit();
        } else {
            // Si el correo no existe o el número de control está mal
            header("Location: ../../index.php?error=credenciales");
            exit();
        }

    } catch (Exception $e) {
        // En PHP 8.1 los errores de PDO son excepciones por defecto
        echo "<h3>⚠️ Error de autenticación (SGEI):</h3>";
        echo "<pre>" . $e->getMessage() . "</pre>";
        die(); 
    }
}