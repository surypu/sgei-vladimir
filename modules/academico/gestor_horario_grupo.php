<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/sgei/config/Database.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') { header("Location: ../../index.php"); exit(); }

$db = (new \SGEI\Config\Database())->connect();

// 1. Obtener lista de grupos únicos que existen en la tabla alumnos
$grupos = $db->query("SELECT DISTINCT grupo FROM alumnos WHERE grupo IS NOT NULL AND grupo != ''")->fetchAll(PDO::FETCH_ASSOC);

$grupo_seleccionado = isset($_GET['grupo']) ? $_GET['grupo'] : '';
$materias_grupo = [];

if ($grupo_seleccionado) {
    // 2. Obtener materias que tienen alumnos de ese grupo (para saber qué materias cargar)
    // O si prefieres, simplemente cargar todas las materias disponibles
    $materias_grupo = $db->query("SELECT * FROM materias ORDER BY nombre_materia ASC")->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SGEI - Asignar Horarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light p-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold"><i class="bi bi-calendar-plus text-primary"></i> Asignador de Horarios</h3>
            <a href="../Escolar/control_escolar.php" class="btn btn-secondary btn-sm">Volver</a>
        </div>

        <div class="card shadow-sm p-4 mb-4 border-0">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label class="form-label fw-bold">Selecciona el Grupo para trabajar:</label>
                    <select name="grupo" class="form-select form-select-lg" onchange="this.form.submit()">
                        <option value="">-- Elige un grupo --</option>
                        <?php foreach($grupos as $g): ?>
                            <option value="<?= $g['grupo'] ?>" <?= $grupo_seleccionado == $g['grupo'] ? 'selected' : '' ?>>
                                GRUPO <?= $g['grupo'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 text-muted small">
                    <i class="bi bi-info-circle"></i> Solo aparecen grupos que ya tienen alumnos registrados.
                </div>
            </form>
        </div>

        <?php if ($grupo_seleccionado): ?>
        <div class="alert alert-info border-0 shadow-sm">
            Estás configurando el horario para el <strong>Grupo: <?= $grupo_seleccionado ?></strong>
        </div>

        <div class="row">
            <?php foreach($materias_grupo as $mat): ?>
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-header bg-white fw-bold text-primary">
                        <i class="bi bi-book"></i> <?= $mat['nombre_materia'] ?>
                    </div>
                    <div class="card-body">
                        <form action="gestion_horarios.php" method="POST">
                            <input type="hidden" name="id_materia" value="<?= $mat['id_materia'] ?>">
                            <input type="hidden" name="grupo" value="<?= $grupo_seleccionado ?>">
                            
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="small fw-bold">Día:</label>
                                    <select name="dia" class="form-select form-select-sm">
                                        <option>Lunes</option><option>Martes</option><option>Miércoles</option><option>Jueves</option><option>Viernes</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="small fw-bold">Aula:</label>
                                    <input type="text" name="aula" class="form-control form-select-sm" placeholder="Aula..." required>
                                </div>
                                <div class="col-6">
                                    <label class="small fw-bold">Hora Inicio:</label>
                                    <input type="time" name="inicio" class="form-control form-select-sm" required>
                                </div>
                                <div class="col-6">
                                    <label class="small fw-bold">Hora Fin:</label>
                                    <input type="time" name="fin" class="form-control form-select-sm" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-outline-primary btn-sm w-100 mt-3">
                                <i class="bi bi-plus-circle"></i> Agregar a este día
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>