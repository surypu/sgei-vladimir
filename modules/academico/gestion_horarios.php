<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/sgei/config/Database.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') { 
    header("Location: ../../index.php"); 
    exit(); 
}

$db = (new \SGEI\Config\Database())->connect();

// Lógica para Guardar Horario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_materia'])) {
    $stmt = $db->prepare("INSERT INTO horarios (id_materia, grupo, dia_semana, hora_inicio, hora_fin, aula) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['id_materia'], $_POST['grupo'], $_POST['dia'], $_POST['inicio'], $_POST['fin'], $_POST['aula']]);
    header("Location: gestion_horarios.php?msj=ok"); 
    exit();
}

// Lógica para Eliminar
if (isset($_GET['del'])) {
    $db->prepare("DELETE FROM horarios WHERE id_horario = ?")->execute([$_GET['del']]);
    header("Location: gestion_horarios.php"); 
    exit();
}

$materias = $db->query("SELECT * FROM materias ORDER BY nombre_materia ASC")->fetchAll();
$horarios = $db->query("SELECT h.*, m.nombre_materia FROM horarios h JOIN materias m ON h.id_materia = m.id_materia ORDER BY FIELD(dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'), hora_inicio")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SGEI - Gestion de Horarios</title>
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

        /* Encabezado */
        .page-title { font-weight: 700; color: var(--azul-base); }

        /* Tarjetas */
        .card-custom { 
            border: none; 
            border-radius: 12px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.05); 
            background: var(--blanco);
        }

        .header-admin { 
            background: var(--azul-base); 
            color: white; 
            border-radius: 12px 12px 0 0; 
            font-weight: 600;
        }

        /* Botones */
        .btn-primary { 
            background-color: var(--azul-vibrante); 
            border: none; 
            font-weight: 600; 
            padding: 10px;
        }

        .btn-secondary { background-color: #6c757d; border: none; font-size: 0.9rem; }

        /* Tablas */
        .table thead { background-color: var(--azul-base); color: white; }
        .table-hover tbody tr:hover { background-color: #f8fafc; }
        .badge-grupo { background: #e9ecef; color: var(--azul-base); font-weight: 600; border: 1px solid #dee2e6; }
    </style>
</head>
<body class="p-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="page-title m-0">Gestion de Horarios</h2>
                <p class="text-muted small m-0">Organizacion de bloques academicos por grupo y aula.</p>
            </div>
            <a href="../Escolar/control_escolar.php" class="btn btn-secondary shadow-sm">
                Regresar al Panel
            </a>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card card-custom">
                    <div class="card-header header-admin p-3 text-center">
                        Asignar Nuevo Bloque
                    </div>
                    <div class="card-body p-4">
                        <form method="POST">
                            <div class="mb-2">
                                <label class="form-label fw-bold small">Materia</label>
                                <select name="id_materia" class="form-select" required>
                                    <option value="" disabled selected>Seleccione materia</option>
                                    <?php foreach($materias as $m): ?>
                                        <option value="<?= $m['id_materia'] ?>"><?= $m['nombre_materia'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-bold small">Grupo</label>
                                <input type="text" name="grupo" class="form-control" placeholder="Ej: 4-A" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-bold small">Dia</label>
                                <select name="dia" class="form-select">
                                    <option>Lunes</option><option>Martes</option><option>Miércoles</option><option>Jueves</option><option>Viernes</option>
                                </select>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6">
                                    <label class="form-label fw-bold small">Inicio</label>
                                    <input type="time" name="inicio" class="form-control" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold small">Fin</label>
                                    <input type="time" name="fin" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Aula</label>
                                <input type="text" name="aula" class="form-control" placeholder="Ej: A-11" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                Registrar en Horario
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card card-custom overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-center">
                            <thead>
                                <tr>
                                    <th class="p-3">Dia</th>
                                    <th>Hora</th>
                                    <th>Materia</th>
                                    <th>Grupo</th>
                                    <th>Aula</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($horarios as $h): ?>
                                <tr>
                                    <td class="fw-bold"><?= $h['dia_semana'] ?></td>
                                    <td class="small text-muted"><?= substr($h['hora_inicio'],0,5) ?> - <?= substr($h['hora_fin'],0,5) ?></td>
                                    <td class="fw-bold text-dark"><?= $h['nombre_materia'] ?></td>
                                    <td><span class="badge badge-grupo px-3 py-2"><?= $h['grupo'] ?></span></td>
                                    <td><?= $h['aula'] ?></td>
                                    <td>
                                        <a href="?del=<?= $h['id_horario'] ?>" 
                                           class="text-danger" 
                                           onclick="return confirm('¿Eliminar este bloque de horario?')">
                                            <i class="bi bi-trash3-fill"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>

                                <?php if(empty($horarios)): ?>
                                <tr>
                                    <td colspan="6" class="py-5 text-muted small">
                                        No hay horarios registrados para ningun grupo.
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="text-center mt-5 text-muted small">
        SGEI Vladimir | Gestion de Tiempos y Espacios 2026
    </footer>
</body>
</html>