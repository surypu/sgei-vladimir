<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/sgei/config/Database.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'alumno') {
    exit("Acceso denegado");
}

$database = new \SGEI\Config\Database();
$db = $database->connect();
$id_alumno = $_SESSION['id'];

// Consultamos las materias y notas del alumno
$sql = "SELECT m.nombre_materia, m.creditos, c.parcial1, c.parcial2, c.parcial3, a.grupo 
        FROM calificaciones c
        JOIN materias m ON c.id_materia = m.id_materia
        JOIN alumnos a ON c.id_alumno = a.id_alumno
        WHERE c.id_alumno = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$id_alumno]);
$materias = $stmt->fetchAll(PDO::FETCH_ASSOC);

$nombre_alumno = $_SESSION['nombre'];
$grupo = !empty($materias) ? $materias[0]['grupo'] : "Sin asignar";
$fecha = date("d/m/Y");

// --- ELIMINAMOS LOS HEADERS DE PDF PARA EVITAR EL ERROR ---
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Académico - <?= $nombre_alumno ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 40px; color: #333; }
        .header { text-align: center; border-bottom: 3px solid #0d6efd; padding-bottom: 20px; margin-bottom: 30px; }
        .logo-placeholder { font-size: 24px; font-weight: bold; color: #0d6efd; text-transform: uppercase; }
        .info-table { width: 100%; margin-bottom: 30px; border: none; }
        .info-table td { padding: 5px; border: none; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
        th, td { border: 1px solid #dee2e6; padding: 12px; text-align: center; }
        th { background-color: #f8f9fa; color: #0d6efd; font-weight: bold; text-transform: uppercase; font-size: 12px; }
        .materia-name { text-align: left; font-weight: bold; }
        .promedio { background-color: #e7f1ff; font-weight: bold; }
        .footer { margin-top: 60px; text-align: center; font-size: 12px; color: #777; }
        .signature { margin-top: 40px; border-top: 1px solid #333; width: 250px; margin-left: auto; margin-right: auto; padding-top: 10px; }
        
        /* Ocultar el botón de imprimir al generar el PDF */
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="text-align: right; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #198754; color: white; border: none; border-radius: 5px; cursor: pointer;">
            <b> Confirmar Guardado / Imprimir</b>
        </button>
    </div>

    <div class="header">
        <div class="logo-placeholder"> Sistema de Gestión Escolar Inteligente</div>
        <p style="margin: 5px 0;">Reporte Académico Oficial de Calificaciones</p>
    </div>

    <table class="info-table">
        <tr>
            <td><strong>ESTUDIANTE:</strong> <?= strtoupper($nombre_alumno) ?></td>
            <td style="text-align: right;"><strong>FECHA:</strong> <?= $fecha ?></td>
        </tr>
        <tr>
            <td><strong>GRUPO:</strong> <?= $grupo ?></td>
            <td style="text-align: right;"><strong>ESTADO:</strong> REGULAR</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th class="materia-name">Asignatura</th>
                <th>Créditos</th>
                <th>Parcial 1</th>
                <th>Parcial 2</th>
                <th>Parcial 3</th>
                <th class="promedio">Final</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $gran_promedio = 0;
            $total_materias = count($materias);
            foreach($materias as $m): 
                $prom = ($m['parcial1'] + $m['parcial2'] + $m['parcial3']) / 3;
                $gran_promedio += $prom;
            ?>
            <tr>
                <td class="materia-name"><?= $m['nombre_materia'] ?></td>
                <td><?= $m['creditos'] ?></td>
                <td><?= $m['parcial1'] ?></td>
                <td><?= $m['parcial2'] ?></td>
                <td><?= $m['parcial3'] ?></td>
                <td class="promedio"><?= number_format($prom, 1) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top: 20px; text-align: right;">
        <strong>PROMEDIO GENERAL: <?= $total_materias > 0 ? number_format($gran_promedio / $total_materias, 1) : '0.0' ?></strong>
    </div>

    <div class="footer">
        <div class="signature">Sello y Firma de Servicios Escolares</div>
        <p style="margin-top: 20px;">Este documento es para fines informativos y carece de validez sin el sello oficial de la institución.</p>
    </div>

    <script>
        // Abrir diálogo de impresión automáticamente al cargar
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>