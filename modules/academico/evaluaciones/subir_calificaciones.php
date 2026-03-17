<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/sgei/config/Database.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

$database = new \SGEI\Config\Database();
$db = $database->connect();

$sqlAlumnos = "SELECT a.id_alumno, u.nombre_completo FROM alumnos a JOIN usuarios u ON a.id_alumno = u.id_usuario";
$alumnos = $db->query($sqlAlumnos)->fetchAll();

$materias = $db->query("SELECT * FROM materias")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id_alumno = $_POST['id_alumno'];
        $id_materia = $_POST['id_materia'];
        $nota = $_POST['nota'];
        $parcial = $_POST['parcial']; 

        if(empty($id_alumno)) throw new Exception("Selecciona un alumno valido.");

        $sql = "INSERT INTO calificaciones (id_alumno, id_materia, $parcial) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE $parcial = VALUES($parcial)";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$id_alumno, $id_materia, $nota]);
        
        echo "<script>
                alert('Registro de " . strtoupper($parcial) . " guardado correctamente'); 
                window.location.href='../../Escolar/control_escolar.php';
              </script>";
        exit();
    } catch (Exception $e) {
        echo "<div class='alert alert-danger m-3 small'>Error: " . $e->getMessage() . "</div>";
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
        <div class="card card-custom overflow-hidden">
            <div class="card-header-vlad text-center">
                <h4 class="m-0 fw-bold">Captura de Calificaciones</h4>
                <p class="small m-0 opacity-75">Asignacion de rendimiento por parcial</p>
            </div>
            <div class="card-body p-4">
                <form method="POST" autocomplete="off">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-uppercase">Estudiante</label>
                        <input class="form-control" list="listaAlumnos" id="inputAlumno" placeholder="Buscar nombre del alumno..." required>
                        <datalist id="listaAlumnos">
                            <?php foreach($alumnos as $al): ?>
                                <option data-id="<?= $al['id_alumno'] ?>" value="<?= $al['nombre_completo'] ?>">
                            <?php endforeach; ?>
                        </datalist>
                        <input type="hidden" name="id_alumno" id="id_alumno_hidden">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-uppercase">Asignatura</label>
                        <select name="id_materia" class="form-select" required>
                            <option value="" disabled selected>Seleccione materia</option>
                            <?php foreach($materias as $ma): ?>
                                <option value="<?= $ma['id_materia'] ?>"><?= $ma['nombre_materia'] ?></option>
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
                            <label class="form-label fw-bold text-uppercase">Calificacion</label>
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