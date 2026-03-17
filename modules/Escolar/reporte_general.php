<?php
// modules/escolar/reporte_general.php
session_start();
require_once '../../config/Database.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

$db = (new SGEI\Config\Database())->connect();

// CONSULTA AVANZADA (JOIN) - Requerimiento de la Rúbrica
// Unimos alumnos, usuarios y calificaciones para sacar promedios
$sql = "SELECT 
            u.nombre_completo, 
            a.numero_control, 
            AVG(c.calificaciones) as promedio,
            COUNT(c.id_materia) as materias_cursadas
        FROM alumnos a
        INNER JOIN usuarios u ON a.id_alumno = u.id_usuario
        LEFT JOIN calificaciones c ON a.id_alumnos = c.id_alumnos
        GROUP BY a.id_alumnos";

$stmt = $db->query($sql);
$reportes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SGEI - Reporte General</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-file-earmark-bar-graph"></i> Reporte de Rendimiento Académico</h2>
            <button onclick="window.print()" class="btn btn-danger">Imprimir a PDF</button>
        </div>

        <div class="card shadow border-0">
            <div class="card-body">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>N° Control</th>
                            <th>Alumno</th>
                            <th>Materias</th>
                            <th>Promedio General</th>
                            <th>Estatus</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($reportes as $r): ?>
                        <tr>
                            <td><?= $r['numero_control'] ?></td>
                            <td><?= $r['nombre_completo'] ?></td>
                            <td><?= $r['materias_cursadas'] ?></td>
                            <td><strong><?= number_format($r['promedio'], 1) ?></strong></td>
                            <td>
                                <?php 
                                    $promedio = (float)$r['promedio'];
                                    echo match(true) {
                                        $promedio >= 9.0 => '<span class="badge bg-success">Excelente</span>',
                                        $promedio >= 7.0 => '<span class="badge bg-warning text-dark">Regular</span>',
                                        $promedio > 0    => '<span class="badge bg-danger">Riesgo</span>',
                                        default          => '<span class="badge bg-secondary">Sin Datos</span>',
                                    };
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3">
            <a href="control_escolar.php" class="btn btn-secondary text-white">Volver al Panel</a>
        </div>
    </div>
</body>
</html>