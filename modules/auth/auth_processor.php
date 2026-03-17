<?php
session_start();
require_once '../../config/Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = filter_var(trim($_POST['correo']), FILTER_SANITIZE_EMAIL);
    $pass = trim($_POST['password']); // Aquí el alumno escribirá su número de control

    // 1. --- VALIDACIÓN PARA EL ADMIN (Hardcoded para que nunca pierdas acceso) ---
    if ($correo == 'admin@bladimir.edu' && $pass == '12345') {
        $_SESSION['id'] = 1;
        $_SESSION['nombre'] = "Admin Bladimir";
        $_SESSION['rol'] = 'admin';
        header("Location: ../Escolar/control_escolar.php"); 
        exit();
    } 

    // 2. --- VALIDACIÓN PARA ALUMNOS (Usando el Hash del número de control) ---
    try {
        $database = new \SGEI\Config\Database();
        $db = $database->connect();

        // Buscamos al usuario por su correo
        $stmt = $db->prepare("SELECT id_usuario, nombre_completo, rol, password FROM usuarios WHERE correo = ?");
        $stmt->execute([$correo]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // password_verify toma lo que escribió el alumno ($pass) y lo compara con el hash de la DB
        if ($user && password_verify($pass, $user['password'])) {
            
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
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        // ESTA LÍNEA ES LA CLAVE:
        echo "<h3>⚠️ Error al guardar:</h3>";
        echo "<pre>" . $e->getMessage() . "</pre>";
        die(); 
    }
}