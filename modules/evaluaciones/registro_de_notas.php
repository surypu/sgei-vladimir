<?php
// modules/evaluaciones/registro_notas.php
session_start();
require_once '../../config/Database.php';

// Seguridad: Solo Admin y Maestros pueden evaluar
if (!isset($_SESSION['rol']) || $_SESSION['rol'] === 'alumno') {
    header("Location: ../../index.php");
    exit();
}

$db = (new SGEI\Config\Database())->connect();

// Consulta avanzada con JOIN para ver qué alumno pertenece a qué usuario
$sql = "SELECT a.id_alumno, u.nombre_completo, a.numero_control 
        FROM alumnos a 
        INNER JOIN usuarios u ON a.id_alumno = u.id_usuario";
$alumnos = $db->query($sql)->fetchAll();

// Traer materias para el combo box
$materias = $db->query("SELECT * FROM materias")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SGEI - Registro de Calificaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">SGEI - Evaluaciones</a>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">Asignar Calificación</div>
                    <div class="card-body">
                        <form action="guardar_nota.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Alumno</label>
                                <select name="id_alumno" class="form-select" required>
                                    <?php foreach($alumnos as $al): ?>
                                        <option value="<?= $al['id_alumno'] ?>"><?= $al['nombre_completo'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Materia</label>
                                <select name="id_materia" class="form-select" required>
                                    <?php foreach($materias as $mat): ?>
                                        <option value="<?= $mat['id_materia'] ?>"><?= $mat['nombre_materia'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Calificación (0-10)</label>
                                <input type="number" name="nota" step="0.1" min="0" max="10" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Registrar Evaluación</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header">Historial Reciente</div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Alumno</th>
                                    <th>Materia</th>
                                    <th>Nota</th>
                                    <th>Estatus</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td colspan="4" class="text-center text-muted">Selecciona un alumno para ver su historial</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>