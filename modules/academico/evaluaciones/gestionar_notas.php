<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/sgei/config/Database.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../../index.php");
    exit();
}

$db = (new \SGEI\Config\Database())->connect();

// Lógica para Eliminar Calificación
if (isset($_GET['eliminar_id'])) {
    $id_eliminar = $_GET['eliminar_id'];
    try {
        $stmtDel = $db->prepare("DELETE FROM calificaciones WHERE id_calificacion = ?");
        $stmtDel->execute([$id_eliminar]);
        echo "<script>alert('Registro eliminado correctamente'); window.location='gestionar_notas.php';</script>";
    } catch (Exception $e) {
        echo "<script>alert('Error al eliminar');</script>";
    }
}

$sql = "SELECT c.id_calificacion, u.nombre_completo, m.nombre_materia, a.grupo, c.parcial1, c.parcial2, c.parcial3 
        FROM calificaciones c
        JOIN alumnos a ON c.id_alumno = a.id_alumno
        JOIN usuarios u ON a.id_alumno = u.id_usuario
        JOIN materias m ON c.id_materia = m.id_materia
        ORDER BY u.nombre_completo ASC";
$notas = $db->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SGEI - Gestion de Notas</title>
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

        .card-custom { 
            border: none; 
            border-radius: 12px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.05); 
            background: var(--blanco);
        }

        .table thead { 
            background-color: var(--azul-base); 
            color: var(--blanco); 
        }

        .search-container {
            border-radius: 8px;
            border: 1px solid #d1d9e0;
            background: var(--blanco);
        }

        .search-container i { color: #adb5bd; }

        .btn-outline-secondary { border-radius: 8px; font-size: 0.9rem; }
        
        .badge-grupo { 
            background: #e9ecef; 
            color: var(--azul-base); 
            font-weight: 600; 
            border: 1px solid #dee2e6; 
        }

        .text-vibrante { color: var(--azul-vibrante) !important; font-weight: 700; }
    </style>
</head>
<body class="p-4">
    <div class="container card-custom p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold m-0" style="color: var(--azul-base);">Control de Parciales</h2>
                <p class="text-muted small m-0">Administracion y seguimiento de rendimiento academico.</p>
            </div>
            <a href="../../Escolar/control_escolar.php" class="btn btn-outline-secondary shadow-sm">
                Regresar al Panel
            </a>
        </div>

        <div class="input-group search-container mb-3 shadow-sm p-1">
            <span class="input-group-text bg-transparent border-0"><i class="bi bi-search"></i></span>
            <input type="text" id="buscador" class="form-control border-0" placeholder="Buscar por nombre o materia...">
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle" id="tablaNotas">
                <thead>
                    <tr>
                        <th class="ps-3">Alumno</th>
                        <th>Grupo</th>
                        <th>Materia</th>
                        <th class="text-center">P1</th>
                        <th class="text-center">P2</th>
                        <th class="text-center">P3</th>
                        <th class="text-center">Promedio</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($notas as $n): 
                        $promedio = ($n['parcial1'] + $n['parcial2'] + $n['parcial3']) / 3;
                    ?>
                    <tr class="fila-alumno">
                        <td class="nombre-alumno ps-3 fw-bold text-dark"><?= $n['nombre_completo'] ?></td>
                        <td><span class="badge badge-grupo px-3 py-2"><?= $n['grupo'] ?></span></td>
                        <td class="nombre-materia text-muted"><?= $n['nombre_materia'] ?></td>
                        <td class="text-center"><?= $n['parcial1'] ?></td>
                        <td class="text-center"><?= $n['parcial2'] ?></td>
                        <td class="text-center"><?= $n['parcial3'] ?></td>
                        <td class="text-center fw-bold <?= $promedio < 6 ? 'text-danger' : 'text-vibrante' ?>">
                            <?= number_format($promedio, 1) ?>
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <a href="editar_notas.php?id=<?= $n['id_calificacion'] ?>" class="btn btn-link text-decoration-none text-warning p-1" title="Editar">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <a href="gestionar_notas.php?eliminar_id=<?= $n['id_calificacion'] ?>" 
                                   class="btn btn-link text-decoration-none text-danger p-1" 
                                   onclick="return confirm('Confirmar eliminacion de calificacion?')">
                                    <i class="bi bi-trash-fill"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.getElementById('buscador').addEventListener('keyup', function() {
            let filtro = this.value.toLowerCase();
            let filas = document.querySelectorAll('.fila-alumno');

            filas.forEach(fila => {
                let textoAlumno = fila.querySelector('.nombre-alumno').textContent.toLowerCase();
                let textoMateria = fila.querySelector('.nombre-materia').textContent.toLowerCase();
                
                if (textoAlumno.includes(filtro) || textoMateria.includes(filtro)) {
                    fila.style.display = "";
                } else {
                    fila.style.display = "none";
                }
            });
        });
    </script>
</body>
</html>