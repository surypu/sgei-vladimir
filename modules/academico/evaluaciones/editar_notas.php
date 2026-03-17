<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/sgei/config/Database.php';
$db = (new \SGEI\Config\Database())->connect();

$id = $_GET['id'];

// Obtener datos actuales
$stmt = $db->prepare("SELECT c.*, a.id_alumno, a.grupo FROM calificaciones c JOIN alumnos a ON c.id_alumno = a.id_alumno WHERE c.id_calificacion = ?");
$stmt->execute([$id]);
$data = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Actualizar Notas
    $updNota = $db->prepare("UPDATE calificaciones SET parcial1 = ?, parcial2 = ?, parcial3 = ? WHERE id_calificacion = ?");
    $updNota->execute([$_POST['p1'], $_POST['p2'], $_POST['p3'], $id]);

    // 2. Actualizar Grupo del Alumno
    $updGrupo = $db->prepare("UPDATE alumnos SET grupo = ? WHERE id_alumno = ?");
    $updGrupo->execute([$_POST['grupo'], $data['id_alumno']]);

    header("Location: gestionar_notas.php?msj=actualizado");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5">
    <div class="container col-md-4 card p-4 shadow">
        <h4>Editar Parciales y Grupo</h4>
        <form method="POST">
            <label>Grupo Actual:</label>
            <input type="text" name="grupo" class="form-control mb-3" value="<?= $data['grupo'] ?>">
            
            <label>Parcial 1:</label>
            <input type="number" step="0.1" name="p1" class="form-control mb-2" value="<?= $data['parcial1'] ?>">
            
            <label>Parcial 2:</label>
            <input type="number" step="0.1" name="p2" class="form-control mb-2" value="<?= $data['parcial2'] ?>">
            
            <label>Parcial 3:</label>
            <input type="number" step="0.1" name="p3" class="form-control mb-4" value="<?= $data['parcial3'] ?>">

            <button class="btn btn-primary w-100">Guardar Cambios</button>
        </form>
    </div>
</body>
</html>