<?php
session_start();
/** @var \SGEI\Config\Database $db */
require_once $_SERVER['DOCUMENT_ROOT'] . '/sgei/config/Database.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

$database = new \SGEI\Config\Database();
$db = $database->connect();

// PHP 8.1: FETCH_ASSOC por defecto para evitar duplicidad de datos en el array
$sqlAlumnos = "SELECT a.id_alumno, u.nombre_completo FROM alumnos a JOIN usuarios u ON a.id_alumno = u.id_usuario";
$alumnos = $db->query($sqlAlumnos)->fetchAll(PDO::FETCH_ASSOC);

$materias = $db->query("SELECT * FROM materias")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // PHP 8.1: Aseguramos tipos de datos desde el inicio
        $id_alumno = (int)($_POST['id_alumno'] ?? 0);
        $id_materia = (int)($_POST['id_materia'] ?? 0);
        $nota = (float)($_POST['nota'] ?? 0);
        $parcial_solicitado = $_POST['parcial'] ?? '';

        // Validación de White-list para el nombre de la columna (seguridad extra)
        $parciales_validos = ['parcial1', 'parcial2', 'parcial3'];
        if (!in_array($parcial_solicitado, $parciales_validos)) {
            throw new Exception("Periodo no válido.");
        }

        if(empty($id_alumno)) throw new Exception("Selecciona un alumno válido de la lista.");
        if(empty($id_materia)) throw new Exception("Selecciona una asignatura.");

        // Usamos la variable validada para la columna
        $sql = "INSERT INTO calificaciones (id_alumno, id_materia, $parcial_solicitado) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE $parcial_solicitado = VALUES($parcial_solicitado)";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$id_alumno, $id_materia, $nota]);
        
        echo "<script>
                alert('Registro de " . strtoupper($parcial_solicitado) . " guardado correctamente'); 
                window.location.href='../../Escolar/control_escolar.php';
              </script>";
        exit();
    } catch (Exception $e) {
        $error_msj = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SGEI - Registro de Calificaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --azul-base: #1a1a2e;    
            --azul-vibrante: #0d6efd; 
            --azul-fondo: #f0f4f8;     
            --blanco: #ffffff;
            --texto: #2c3e50;
        }

        body { 
            background-color: var(--azul-fondo); 
            font-family: 'Segoe UI', sans-serif;
            color: var(--texto);
        }

        .card-custom { 
            border-radius: 12px; 
            border: none; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            background: var(--blanco);
        }

        .card-header-vlad {
            background-color: var(--azul-base);
            color: var(--blanco);
            border-radius: 12px 12px 0 0 !important;
            padding: 20px;
        }

        .form-label {
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            color: #6c757d;
        }

        .form-control, .form-select {
            border-radius: 8px;
            padding: 10px;
            border: 1px solid #d1d9e0;
        }

        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
            border-color: var(--azul-vibrante);
        }

        .btn-primary { 
            background-color: var(--azul-vibrante); 
            border: none;
            padding: 12px;
            font-weight: 600;
            border-radius: 8px;
            transition: 0.3s;
        }

        .btn-primary:hover {
            background-color: #0b5ed7;
            transform: translateY(-2px);
        }

        .btn-link { color: #6c757d; font-size: 0.85rem; }
    </style>
</head>
<body class="p-4">

    <div class="container col-md-5 mt-4">
        <?php if(isset($error_msj)): ?>
            <div class='alert alert-danger shadow-sm mb-3'><i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($error_msj) ?></div>
        <?php endif; ?>

        <div class="card card-custom overflow-hidden">
            <div class="card-header-vlad text-center">
                <h4 class="m-0 fw-bold">Captura de Calificaciones</h4>
                <p class="small m-0 opacity-75">Asignación de rendimiento por parcial</p>
            </div>
            <div class="card-body p-4">
                <form method="POST" autocomplete="off">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-uppercase">Estudiante</label>
                        <input class="form-control" list="listaAlumnos" id="inputAlumno" placeholder="Buscar nombre del alumno..." required>
                        <datalist id="listaAlumnos">
                            <?php foreach($alumnos as $al): ?>
                                <option data-id="<?= $al['id_alumno'] ?>" value="<?= htmlspecialchars((string)$al['nombre_completo']) ?>">
                            <?php endforeach; ?>
                        </datalist>
                        <input type="hidden" name="id_alumno" id="id_alumno_hidden">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-uppercase">Asignatura</label>
                        <select name="id_materia" class="form-select" required>
                            <option value="" disabled selected>Seleccione materia</option>
                            <?php foreach($materias as $ma): ?>
                                <option value="<?= $ma['id_materia'] ?>"><?= htmlspecialchars((string)$ma['nombre_materia']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-uppercase">Periodo</label>
                            <select name="parcial" class="form-select fw-bold text-primary" required>
                                <option value="parcial1">Parcial 1</option>
                                <option value="parcial2">Parcial 2</option>
                                <option value="parcial3">Parcial 3</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-uppercase">Calificación</label>
                            <input type="number" name="nota" step="0.1" class="form-control" min="0" max="10" placeholder="0.0" required>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary shadow-sm">
                            Guardar Registro
                        </button>
                        <a href="../../Escolar/control_escolar.php" class="btn btn-link text-decoration-none text-center">
                            Regresar al panel anterior
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('inputAlumno').addEventListener('input', function(e) {
            var input = e.target,
                list = input.getAttribute('list'),
                options = document.querySelectorAll('#' + list + ' option'),
                hiddenInput = document.getElementById('id_alumno_hidden'),
                inputValue = input.value;
            hiddenInput.value = "";
            for(var i = 0; i < options.length; i++) {
                if(options[i].value === inputValue) {
                    hiddenInput.value = options[i].getAttribute('data-id');
                    break;
                }
            }
        });
    </script>
</body>
</html>