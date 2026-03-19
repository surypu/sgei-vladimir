<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once "../../config/Database.php"; 

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'alumno') {
    header("Location: ../../index.php");
    exit();
}

try {
    $database = new \SGEI\Config\Database();
    $db = $database->connect();
    $id_alumno = (int)($_SESSION['id'] ?? 0); 

    // 1. Obtener Calificaciones
    $sql = "SELECT m.nombre_materia, c.parcial1, c.parcial2, c.parcial3, a.grupo
            FROM calificaciones c
            JOIN materias m ON c.id_materia = m.id_materia
            JOIN alumnos a ON c.id_alumno = a.id_alumno
            WHERE c.id_alumno = ?";

    $stmt = $db->prepare($sql);
    $stmt->execute([$id_alumno]);
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Determinar Grupo para el Horario
    if (empty($datos)) {
        $stmtG = $db->prepare("SELECT grupo FROM alumnos WHERE id_alumno = ?");
        $stmtG->execute([$id_alumno]);
        $resG = $stmtG->fetch(PDO::FETCH_ASSOC);
        $grupo = (string)($resG['grupo'] ?? "N/A");
    } else {
        $grupo = (string)$datos[0]['grupo'];
    }

    // 3. Obtener Horarios del Grupo
    $sqlH = "SELECT h.*, m.nombre_materia 
             FROM horarios h 
             JOIN materias m ON h.id_materia = m.id_materia 
             WHERE h.grupo = ? 
             ORDER BY FIELD(dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'), hora_inicio";
    $stmtH = $db->prepare($sqlH);
    $stmtH->execute([$grupo]);
    $horarios = $stmtH->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Error crítico: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SGEI - Portal Académico</title>
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
        body { background-color: var(--azul-fondo); font-family: 'Segoe UI', sans-serif; color: var(--texto); }
        .sidebar { height: 100vh; background: var(--azul-base); color: white; position: fixed; width: 250px; padding-top: 20px; z-index: 100; }
        .main-content { margin-left: 250px; padding: 40px; }
        .card-custom { border-radius: 12px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); background: white; margin-bottom: 20px; }
        .promedio-circle { 
            width: 100px; height: 100px; border-radius: 50%; background: #eef2ff; 
            display: flex; align-items: center; justify-content: center; 
            font-size: 1.5rem; font-weight: 800; border: 4px solid var(--azul-vibrante); color: var(--azul-vibrante); margin: 0 auto;
        }
        .btn-pdf { background: #e63946; color: white; font-weight: 600; border-radius: 8px; transition: 0.3s; text-decoration: none; }
        .btn-pdf:hover { background: #c1121f; color: white; transform: translateY(-2px); }
        .ia-card { background: linear-gradient(135deg, #0d6efd, #003da5); color: white; border-radius: 12px; }
        .horario-item { 
            font-size: 0.75rem; padding: 8px; border-radius: 6px; 
            background: #f8f9fa; border-left: 4px solid var(--azul-vibrante); 
            text-align: left; margin-bottom: 5px;
        }
        .table-horario th { background: #f1f4f9; font-size: 0.85rem; text-transform: uppercase; color: #666; }
    </style>
</head>
<body>

<div class="sidebar d-flex flex-column p-3">
    <div class="text-center mb-4">
        <i class="bi bi-person-circle fs-1"></i>
        <h6 class="mt-2 fw-bold"><?= htmlspecialchars(strtoupper((string)$_SESSION['nombre'])) ?></h6>
        <span class="badge bg-primary">Grupo: <?= $grupo ?></span>
    </div>
    <nav class="nav flex-column px-2">
        <a class="nav-link text-white active" href="#"><i class="bi bi-grid-1x2 me-2"></i> Mi Perfil</a>
        <a class="nav-link text-white-50" href="../auth/logout.php"><i class="bi bi-box-arrow-left me-2"></i> Salir</a>
    </nav>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0">Portal Académico</h2>
            <p class="text-muted">Horarios y Calificaciones</p>
        </div>
        <a href="generar_calificaciones.php" target="_blank" class="btn btn-pdf px-4 py-2 shadow-sm">
            <i class="bi bi-file-earmark-pdf-fill me-2"></i> Reporte PDF
        </a>
    </div>

    <div class="row">
        <div class="col-md-9">
            <div class="card card-custom p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-card-checklist me-2"></i>Mis Calificaciones</h5>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-dark">
                            <tr class="text-center">
                                <th class="text-start">Materia</th>
                                <th>P1</th><th>P2</th><th>P3</th><th>Final</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            <?php 
                            $sumaTotal = 0; $count = count($datos);
                            if($count > 0):
                                foreach($datos as $d): 
                                    $final = ((float)$d['parcial1'] + (float)$d['parcial2'] + (float)$d['parcial3']) / 3;
                                    $sumaTotal += $final;
                            ?>
                                <tr>
                                    <td class="text-start fw-bold"><?= htmlspecialchars($d['nombre_materia']) ?></td>
                                    <td><?= number_format((float)$d['parcial1'], 1) ?></td>
                                    <td><?= number_format((float)$d['parcial2'], 1) ?></td>
                                    <td><?= number_format((float)$d['parcial3'], 1) ?></td>
                                    <td class="text-primary fw-bold"><?= number_format($final, 1) ?></td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr><td colspan="5" class="py-4 text-muted">Notas no disponibles.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card card-custom p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-calendar3 me-2"></i>Horario de Clases</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-horario text-center align-middle">
                        <thead>
                            <tr>
                                <th width="15%">Hora</th>
                                <?php $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes']; 
                                foreach($dias as $dia) echo "<th>$dia</th>"; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($horarios)): ?>
                                <tr><td colspan="6" class="py-4 text-muted">No hay horario asignado para el grupo <?= $grupo ?></td></tr>
                            <?php else: 
                                $horas_unicas = array_unique(array_column($horarios, 'hora_inicio'));
                                foreach($hor_unicas as $hora): ?>
                                    <tr>
                                        <td class="fw-bold bg-light"><?= substr($hora, 0, 5) ?></td>
                                        <?php foreach($dias as $dia): ?>
                                            <td>
                                                <?php foreach($horarios as $h): 
                                                    if($h['hora_inicio'] == $hora && $h['dia_semana'] == $dia): ?>
                                                        <div class="horario-item">
                                                            <div class="fw-bold"><?= htmlspecialchars($h['nombre_materia']) ?></div>
                                                            <div class="text-muted">Aula: <?= htmlspecialchars($h['aula']) ?></div>
                                                        </div>
                                                <?php endif; endforeach; ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-custom p-4 text-center">
                <h6 class="text-muted fw-bold small">PROMEDIO CICLO</h6>
                <div class="promedio-circle my-3 shadow-sm">
                    <?php 
                        $promedioGral = ($count > 0) ? ($sumaTotal / $count) : 0;
                        echo number_format($promedioGral, 1);
                    ?>
                </div>
            </div>

            <div class="card card-custom ia-card p-4 shadow-sm">
                <h6 class="fw-bold small"><i class="bi bi-robot me-2"></i>Asistente IA</h6>
                <p class="small mb-0 opacity-90">
                    <?php 
                    if($count == 0) echo "Pendiente de evaluación.";
                    elseif($promedioGral >= 9) echo "Rendimiento excelente. Sigue así.";
                    elseif($promedioGral >= 7) echo "Buen desempeño. ¡Tú puedes!";
                    else echo "Se sugiere reforzar estudios.";
                    ?>
                </p>
            </div>
        </div>
    </div>
</div>

</body>
</html>