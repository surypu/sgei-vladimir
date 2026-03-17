<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/sgei/config/Database.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'alumno') {
    header("Location: ../../index.php");
    exit();
}

$database = new \SGEI\Config\Database();
$db = $database->connect();
$id_alumno = $_SESSION['id']; 

$sql = "SELECT m.nombre_materia, c.parcial1, c.parcial2, c.parcial3, a.grupo
        FROM calificaciones c
        JOIN materias m ON c.id_materia = m.id_materia
        JOIN alumnos a ON c.id_alumno = a.id_alumno
        WHERE c.id_alumno = ?";

$stmt = $db->prepare($sql);
$stmt->execute([$id_alumno]);
$datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmtPlan = $db->query("SELECT nombre_materia, creditos FROM materias ORDER BY nombre_materia ASC");
$planEstudios = $stmtPlan->fetchAll(PDO::FETCH_ASSOC);

$grupo = !empty($datos) ? $datos[0]['grupo'] : "N/A";
$sqlH = "SELECT h.*, m.nombre_materia 
         FROM horarios h 
         JOIN materias m ON h.id_materia = m.id_materia 
         WHERE h.grupo = ? 
         ORDER BY FIELD(dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'), hora_inicio";
$stmtH = $db->prepare($sqlH);
$stmtH->execute([$grupo]);
$horarios = $stmtH->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SGEI - Portal Academico</title>
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

        .sidebar { 
            height: 100vh; 
            background: var(--azul-base); 
            color: white; 
            position: fixed; 
            width: 250px; 
            z-index: 100;
            padding-top: 20px;
        }

        .main-content { margin-left: 250px; padding: 40px; }

        .card-custom { border-radius: 12px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); background: white; }
        
        .nav-link { color: rgba(255,255,255,0.7); transition: 0.3s; font-size: 0.9rem; margin-bottom: 5px; }
        .nav-link:hover, .nav-link.active { color: white; background: rgba(255,255,255,0.1); border-radius: 8px; }

        .promedio-circle { 
            width: 110px; height: 110px; border-radius: 50%; 
            background: var(--azul-claro); display: flex; align-items: center; 
            justify-content: center; font-size: 1.8rem; font-weight: 800; 
            border: 4px solid var(--azul-vibrante); color: var(--azul-vibrante);
            margin: 0 auto;
        }

        .table thead { background: var(--azul-base); color: white; }
        .horario-item { 
            font-size: 0.7rem; padding: 8px; border-radius: 6px; 
            background: #eef2ff; border-left: 4px solid var(--azul-vibrante); 
            text-align: left;
        }

        .btn-reporte { background: #dc3545; color: white; border: none; font-weight: 600; border-radius: 8px; }
        
        @media (max-width: 768px) {
            .sidebar { width: 100%; height: auto; position: relative; }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>

<div class="sidebar d-flex flex-column p-3">
    <div class="text-center mb-4">
        <div class="mb-2"><i class="bi bi-person-circle fs-1"></i></div>
        <h6 class="m-0 fw-bold"><?= strtoupper($_SESSION['nombre']) ?></h6>
        <span class="badge bg-primary mt-2">Grupo: <?= $grupo ?></span>
    </div>
    <hr class="opacity-25">
    <nav class="nav flex-column px-2">
        <a class="nav-link active" href="#"><i class="bi bi-grid-1x2 me-2"></i> Mi Boleta</a>
        <a class="nav-link" href="#mi-horario"><i class="bi bi-calendar3 me-2"></i> Mi Horario</a>
        <a class="nav-link" href="#plan-estudios"><i class="bi bi-journal-text me-2"></i> Plan de Estudios</a>
    </nav>
    <div class="mt-auto p-2">
        <a href="../auth/logout.php" class="btn btn-outline-light btn-sm w-100 border-0">Cerrar Sesion</a>
    </div>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0">Portal Academico</h2>
            <p class="text-muted small">Seguimiento de calificaciones y actividades.</p>
        </div>
        <a href="generar_calificaciones.php" target="_blank" class="btn btn-reporte px-4 py-2">
            Descargar Boleta PDF
        </a>
    </div>

    <div class="row g-4">
        <div class="col-md-9">
            <div class="card card-custom p-4 mb-4">
                <h5 class="fw-bold mb-4" style="color: var(--azul-base);">Reporte de Calificaciones</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr class="text-center">
                                <th class="text-start">Asignatura</th>
                                <th>P1</th>
                                <th>P2</th>
                                <th>P3</th>
                                <th class="bg-primary text-white border-0">Final</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            <?php 
                            $sumaPromedios = 0;
                            if(empty($datos)): ?>
                                <tr><td colspan="5" class="py-5 text-muted">No se han registrado evaluaciones.</td></tr>
                            <?php else:
                                foreach($datos as $d): 
                                    $promedioMateria = ($d['parcial1'] + $d['parcial2'] + $d['parcial3']) / 3;
                                    $sumaPromedios += $promedioMateria;
                            ?>
                            <tr>
                                <td class="text-start fw-bold"><?= $d['nombre_materia'] ?></td>
                                <td><?= $d['parcial1'] ?></td>
                                <td><?= $d['parcial2'] ?></td>
                                <td><?= $d['parcial3'] ?></td>
                                <td class="fw-bold text-primary fs-5"><?= number_format($promedioMateria, 1) ?></td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="mi-horario" class="card card-custom p-4 mb-4">
                <h5 class="fw-bold mb-4" style="color: var(--azul-base);">Horario Semanal</h5>
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="10%">Hora</th>
                                <?php $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes']; 
                                foreach($dias as $dia) echo "<th>$dia</th>"; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($horarios)): ?>
                                <tr><td colspan="6" class="py-4 text-muted small">Horario no asignado para el grupo <?= $grupo ?></td></tr>
                            <?php else: 
                                foreach($horarios as $h): ?>
                                <tr>
                                    <td class="small fw-bold bg-light"><?= substr($h['hora_inicio'],0,5) ?></td>
                                    <?php foreach($dias as $dia): ?>
                                        <td>
                                            <?php if($h['dia_semana'] == $dia): ?>
                                                <div class="horario-item">
                                                    <div class="fw-bold text-dark"><?= $h['nombre_materia'] ?></div>
                                                    <div class="text-muted small">Aula: <?= $h['aula'] ?></div>
                                                </div>
                                            <?php endif; ?>
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
            <div class="card card-custom p-4 text-center mb-4">
                <span class="text-muted small fw-bold text-uppercase">Promedio Ciclo</span>
                <div class="promedio-circle my-3 shadow-sm">
                    <?= count($datos) > 0 ? number_format($sumaPromedios/count($datos), 1) : '0.0' ?>
                </div>
                <p class="small text-muted mb-0">Rendimiento Academico</p>
            </div>

            <div class="card card-custom p-4 bg-primary text-white">
                <h6 class="fw-bold small mb-3">Asistente IA</h6>
                <p class="small m-0 opacity-75">
                    <?php 
                    $promedioGral = count($datos) > 0 ? $sumaPromedios/count($datos) : 0;
                    if($promedioGral >= 9) echo "Rendimiento excelente. Manten el ritmo actual.";
                    elseif($promedioGral >= 7) echo "Buen desempeño. Refuerza los temas del ultimo parcial.";
                    elseif($promedioGral > 0) echo "Alerta de riesgo. Se recomienda solicitar asesoria.";
                    else echo "Pendiente de registro de notas.";
                    ?>
                </p>
            </div>
        </div>
    </div>
</div>

</body>
</html>