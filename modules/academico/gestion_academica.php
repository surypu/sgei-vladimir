<?php
// modules/academico/gestion_academica.php
session_start();
require_once '../../config/Database.php';

// Verificación de seguridad
if (!isset($_SEION['rol'])) { header("Location: ../../index.php"); exit(); }

$db = (new SGEI\Config\Database())->connect();

// Requerimiento: POO Avanzada
interface Consultable { public function getQuery(): string; }
interface Listable { public function getHeaders(): array; }

// Clase que implementa Intersección de Tipos (Simulada para lógica de negocio)
class AcademicReport implements Consultable, Listable {
    public function getQuery(): string {
        return "SELECT nombre_materia, creditos FROM materias";
    }
    public function getHeaders(): array {
        return ['Materia', 'Créditos Académicos'];
    }
}

$reporte = new AcademicReport();
$materias = $db->query($reporte->getQuery())->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SGEI - Módulo Académico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow border-0">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Módulo Académico - Plan de Estudios</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead class="table-secondary">
                        <tr>
                            <?php foreach($reporte->getHeaders() as $header): ?>
                                <th><?= $header ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($materias as $m): ?>
                        <tr>
                            <td><?= $m['nombre_materia'] ?></td>
                            <td><?= $m['creditos'] ?> pts</td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($materias)): ?>
                            <tr><td colspan="2" class="text-center">No hay materias registradas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <a href="../auth/logout.php" class="btn btn-danger btn-sm">Cerrar Sesión</a>
            </div>
        </div>
    </div>
</body>
</html>