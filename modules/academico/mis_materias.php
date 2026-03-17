<?php
session_start();
// Usamos la ruta maestra para evitar errores de conexión
require_once $_SERVER['DOCUMENT_ROOT'] . '/sgei/config/Database.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') { 
    header("Location: ../../index.php"); 
    exit(); 
}

/** @var \SGEI\Config\Database $db */
$database = new \SGEI\Config\Database();
$db = $database->connect();

// LÓGICA PARA AGREGAR MATERIA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre_materia'])) {
    $nombre = trim($_POST['nombre_materia']);
    $creditos = (int)$_POST['creditos'];

    if (!empty($nombre)) {
        $stmtInsert = $db->prepare("INSERT INTO materias (nombre_materia, creditos) VALUES (?, ?)");
        $stmtInsert->execute([$nombre, $creditos]);
        header("Location: gestion_materias.php"); // Recarga para ver la nueva
        exit();
    }
}

// LÓGICA PARA CONSULTAR
$stmt = $db->query("SELECT * FROM materias");
$materias = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SGEI - Gestión de Materias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light p-4">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card shadow border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Nueva Materia</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Nombre de la Materia</label>
                                <input type="text" name="nombre_materia" class="form-control" placeholder="Ej: Cálculo" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Créditos</label>
                                <input type="number" name="creditos" class="form-control" value="5" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Guardar Materia</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow border-0">
                    <div class="card-header bg-dark text-white d-flex justify-content-between">
                        <h5 class="mb-0">Plan de Estudios Actual</h5>
                        <span class="badge bg-secondary"><?= count($materias) ?> Materias</span>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                        <?php foreach($materias as $m): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-book text-secondary me-2"></i>
                                    <?= $m['nombre_materia'] ?>
                                </div>
                                <span class="badge bg-primary rounded-pill"><?= $m['creditos'] ?> pts</span>
                            </li>
                        <?php endforeach; ?>
                        
                        <?php if(empty($materias)): ?>
                            <li class="list-group-item text-center text-muted">No hay materias registradas.</li>
                        <?php endif; ?>
                        </ul>
                        <div class="mt-4">
                            <a href="../Escolar/control_escolar.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Volver al Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>