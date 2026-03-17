<?php
session_start();
// Usamos la ruta absoluta para evitar el error de redirección
require_once $_SERVER['DOCUMENT_ROOT'] . '/sgei/config/Database.php';

// Seguridad: Solo admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') { 
    header("Location: /sgei/index.php"); 
    exit(); 
}

$database = new \SGEI\Config\Database();
$db = $database->connect();

/**
 * Consulta SQL Avanzada:
 * 1. Trae datos de usuario y alumno.
 * 2. Calcula el promedio de los 3 parciales de todas sus materias.
 * 3. Si no tiene calificaciones, el promedio será 0.
 */
$sql = "SELECT 
            a.numero_control, 
            u.nombre_completo, 
            u.correo, 
            a.grupo,
            IFNULL(AVG((c.parcial1 + c.parcial2 + c.parcial3) / 3), 0) as promedio_general
        FROM alumnos a 
        INNER JOIN usuarios u ON a.id_alumno = u.id_usuario
        LEFT JOIN calificaciones c ON a.id_alumno = c.id_alumno
        GROUP BY a.id_alumno";

$alumnos = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SGEI - Reporte General Detallado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --azul-base: #1a1a2e;    
            --azul-vibrante: #0d6efd; 
            --blanco: #ffffff;
            --texto: #2c3e50;
        }

        body { font-family: 'Segoe UI', sans-serif; color: var(--texto); background-color: #fff; }

        .header-report { 
            background-color: var(--azul-base); 
            color: white; 
            padding: 30px; 
            margin-bottom: 30px; 
            border-radius: 0 0 20px 20px;
        }

        .table thead { 
            background-color: var(--azul-base); 
            color: white; 
            text-transform: uppercase;
            font-size: 0.75rem;
        }

        .badge-grupo {
            background-color: #f0f4f8;
            color: var(--azul-base);
            border: 1px solid #d1d9e0;
            font-weight: bold;
        }

        .promedio-destacado {
            font-weight: 800;
            color: var(--azul-vibrante);
        }

        .btn-print { 
            background-color: var(--azul-vibrante); 
            border: none; 
            font-weight: 600;
        }

        @media print {
            .no-print { display: none !important; }
            .header-report { 
                background-color: #fff !important; 
                color: black !important; 
                border-radius: 0;
                border-bottom: 3px solid #000;
                padding: 10px;
            }
            .table-primary { background-color: #eee !important; }
        }
    </style>
</head>
<body>

    <div class="header-report text-center shadow-sm">
        <h1 class="fw-bold m-0">Escuela Vladimir</h1>
        <p class="small opacity-75">Sistema de Gestion Escolar Inteligente (SGEI)</p>
        <div class="mt-3">
            <span class="badge bg-primary px-3">Reporte de Rendimiento General</span>
            <span class="ms-2 small">Generado el: <?php echo date('d/m/Y H:i'); ?></span>
        </div>
    </div>

    <div class="container">
        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle">
                <thead class="text-center">
                    <tr>
                        <th>Control</th>
                        <th>Nombre Completo</th>
                        <th>Grupo</th>
                        <th>Correo</th>
                        <th>Promedio Gral.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($alumnos as $a): ?>
                    <tr>
                        <td class="text-center small fw-bold"><?= $a['numero_control'] ?></td>
                        <td class="ps-3 fw-bold"><?= $a['nombre_completo'] ?></td>
                        <td class="text-center">
                            <span class="badge badge-grupo"><?= $a['grupo'] ?></span>
                        </td>
                        <td class="small text-muted"><?= $a['correo'] ?></td>
                        <td class="text-center promedio-destacado">
                            <?= number_format($a['promedio_general'], 1) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if(empty($alumnos)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">No hay datos disponibles.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-5 text-center no-print">
            <button onclick="window.print()" class="btn btn-print btn-lg px-5 shadow text-white">Descargar PDF</button>
            <a href="control_escolar.php" class="btn btn-outline-secondary btn-lg ms-2">Volver al Panel</a>
        </div>
    </div>

    <footer class="footer-report text-center mt-5 py-4 text-muted small">
        SGEI Vladimir | Documento Oficial de Control Escolar | <?php echo date('Y'); ?>
    </footer>

</body>
</html>