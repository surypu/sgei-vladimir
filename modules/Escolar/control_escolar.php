<?php
session_start();
/** @var \SGEI\Config\Database $db */
require_once '../../config/Database.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') { 
    header("Location: ../../index.php"); 
    exit(); 
}

// Usamos el nuevo motor compatible con 8.1
$database = new \SGEI\Config\Database();
$db = $database->connect();

if (isset($_GET['delete_id'])) {
    $id_del = $_GET['delete_id'];
    $db->prepare("DELETE FROM usuarios WHERE id_usuario = ?")->execute([$id_del]);
    header("Location: control_escolar.php?msj=eliminado");
    exit();
}

try {
    // Consulta optimizada
    $sql = "SELECT 
                a.id_alumno, 
                a.numero_control, 
                u.nombre_completo, 
                u.correo, 
                a.grupo,
                IFNULL(AVG((c.parcial1 + c.parcial2 + c.parcial3) / 3), 0) as promedio_gral
            FROM alumnos a 
            INNER JOIN usuarios u ON a.id_alumno = u.id_usuario
            LEFT JOIN calificaciones c ON a.id_alumno = c.id_alumno
            GROUP BY a.id_alumno";
    $alumnos = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $alumnos = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SGEI - Dashboard Administrativo</title>
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
        .navbar-custom { background: var(--azul-base); padding: 15px 0; }
        
        .card-menu { 
            border: none; 
            border-radius: 12px; 
            transition: all 0.3s ease; 
            cursor: pointer; 
            color: white;
            position: relative;
            overflow: hidden;
        }
        .card-menu:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.15); }
        
        .bg-vlad-1 { background: #1a1a2e; } 
        .bg-vlad-2 { background: #0d6efd; } 
        .bg-vlad-3 { background: #4e73df; } 
        .bg-vlad-4 { background: #5a5c69; } 
        .bg-vlad-5 { background: #2e59d9; } 

        .card-menu i { opacity: 0.2; position: absolute; right: 10px; bottom: 10px; font-size: 4rem; }
        .card-menu h5 { position: relative; z-index: 2; font-weight: 600; margin: 0; }

        .table-container { background: var(--blanco); border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .table thead { background-color: var(--azul-base); color: white; font-size: 0.85rem; }
        .btn-action { border-radius: 6px; font-size: 0.75rem; font-weight: 600; padding: 5px 10px; }
        .badge-grupo { background-color: #f0f4f8; color: var(--azul-base); border: 1px solid #d1d9e0; font-weight: bold; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#">SGEI VLADIMIR</a>
        <div class="d-flex align-items-center">
            <span class="text-white me-3 d-none d-md-inline small">Sesion: <b><?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?></b></span>
            <a href="../auth/logout.php" class="btn btn-sm btn-outline-light border-0">Cerrar Sesion</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row mb-3 g-3">
        <div class="col-md-4">
            <div class="card card-menu bg-vlad-1 p-4" onclick="location.href='registrar_alumno.php'">
                <h5>Registrar Alumno</h5>
                <i class="bi bi-person-plus"></i>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-menu bg-vlad-2 p-4" onclick="location.href='../academico/gestion_materias.php'">
                <h5>Gestion de Materias</h5>
                <i class="bi bi-journal-text"></i>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-menu bg-vlad-3 p-4" onclick="location.href='reporte_alumnos.php'">
                <h5>Reporte General</h5>
                <i class="bi bi-file-earmark-bar-graph"></i>
            </div>
        </div>
    </div>

    <div class="row mb-4 g-3">
        <div class="col-md-6">
            <div class="card card-menu bg-vlad-4 p-4" onclick="location.href='../academico/evaluaciones/gestionar_notas.php'">
                <h5>Gestion de Notas</h5>
                <i class="bi bi-pencil-square"></i>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-menu bg-vlad-5 p-4" onclick="location.href='../academico/gestion_horarios.php'">
                <h5>Gestion de Horarios</h5>
                <i class="bi bi-calendar3"></i>
            </div>
        </div>
    </div>

    <div class="table-container p-4">
        <h4 class="mb-4 fw-bold">Alumnos Registrados</h4>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle text-center">
                <thead>
                    <tr>
                        <th>Control</th>
                        <th class="text-start">Nombre Completo</th>
                        <th>Grupo</th>
                        <th>Correo Institucional</th>
                        <th>Promedio</th>
                        <th width="180px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($alumnos as $a): ?>
                    <tr>
                        <td><span class="text-muted small fw-bold"><?= htmlspecialchars((string)($a['numero_control'] ?? '')) ?></span></td>
                        <td class="text-start fw-bold"><?= htmlspecialchars((string)($a['nombre_completo'] ?? '')) ?></td>
                        <td><span class="badge badge-grupo px-2 py-1"><?= htmlspecialchars((string)($a['grupo'] ?? 'N/A')) ?></span></td>
                        <td class="small text-muted"><?= htmlspecialchars((string)($a['correo'] ?? '')) ?></td>
                        <td>
                            <?php 
                                // PHP 8.1 FIX: Evitamos error si el promedio es NULL
                                $promedio = (float)($a['promedio_gral'] ?? 0); 
                            ?>
                            <strong class="<?= $promedio < 6 ? 'text-danger' : 'text-primary' ?>">
                                <?= number_format($promedio, 1) ?>
                            </strong>
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-1">
                                <a href="../academico/evaluaciones/subir_calificaciones.php?id_alumno=<?= $a['id_alumno'] ?>" 
                                   class="btn btn-outline-primary btn-action">Notas</a>
                                <a href="control_escolar.php?delete_id=<?= $a['id_alumno'] ?>" 
                                   class="btn btn-danger btn-action" 
                                   onclick="return confirm('¿Confirmar eliminacion?')">Borrar</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<footer class="text-center mt-5 pb-4 text-muted small">
    SGEI Vladimir - Plataforma de Gestion Escolar 2026
</footer>

</body>
</html>