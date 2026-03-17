<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/sgei/config/Database.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') { 
    header("Location: ../../index.php"); 
    exit(); 
}

$database = new \SGEI\Config\Database();
$db = $database->connect();

// LÓGICA PARA AGREGAR MATERIA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre_materia'])) {
    $nombre = trim($_POST['nombre_materia']);
    $creditos = (int)$_POST['creditos'];

    if (!empty($nombre)) {
        $stmtInsert = $db->prepare("INSERT INTO materias (nombre_materia, creditos) VALUES (?, ?)");
        $stmtInsert->execute([$nombre, $creditos]);
        header("Location: gestion_materias.php?msj=agregado");
        exit();
    }
}

// LÓGICA PARA ELIMINAR MATERIA
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    $stmtDel = $db->prepare("DELETE FROM materias WHERE id_materia = ?");
    $stmtDel->execute([$id]);
    header("Location: gestion_materias.php?msj=eliminado");
    exit();
}

$stmt = $db->query("SELECT * FROM materias ORDER BY id_materia DESC");
$materias = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SGEI - Gestion de Materias</title>
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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

        .btn-outline-secondary { border-radius: 8px; font-size: 0.9rem; }

        /* Tablas */
        .table thead { background-color: var(--azul-base); color: white; }
        .table-hover tbody tr:hover { background-color: #f8fafc; }
        .badge-creditos { background: var(--azul-light); color: var(--azul-vibrante); border: 1px solid var(--azul-vibrante); }
    </style>
</head>
<body class="p-4">

<div class="container">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h2 class="page-title">Control de Asignaturas</h2>
            <p class="text-muted small">Administracion del catalogo de materias y unidades valorativas.</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="../Escolar/control_escolar.php" class="btn btn-outline-secondary shadow-sm">
                Regresar al Panel
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card card-custom">
                <div class="card-header header-admin p-3 text-center">
                    Registro de Materia
                </div>
                <div class="card-body p-4">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Nombre de la Materia</label>
                            <input type="text" name="nombre_materia" class="form-control" placeholder="Ej: Matematicas IV" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold small">Creditos</label>
                            <input type="number" name="creditos" class="form-control" value="4" min="1" max="15" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            Guardar Asignatura
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-custom overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="p-3">ID</th>
                                <th>Materia</th>
                                <th class="text-center">Creditos</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($materias as $m): ?>
                            <tr>
                                <td class="p-3 text-muted">#<?= $m['id_materia'] ?></td>
                                <td class="fw-bold"><?= $m['nombre_materia'] ?></td>
                                <td class="text-center">
                                    <span class="badge badge-creditos px-3 py-2"><?= $m['creditos'] ?> pts</span>
                                </td>
                                <td class="text-center">
                                    <a href="?eliminar=<?= $m['id_materia'] ?>" 
                                       class="btn btn-link text-danger p-0" 
                                       onclick="return confirm('Confirmar eliminacion de materia?')">
                                        Eliminar
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>

                            <?php if(empty($materias)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted small">
                                    No se encontraron materias registradas.
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
    Plataforma SGEI | Gestion Academica Vladimir
</footer>

</body>
</html>