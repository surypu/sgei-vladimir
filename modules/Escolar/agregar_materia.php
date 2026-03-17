<?php
// Solo la lógica de inserción rápida para que no te compliques
require_once '../../config/Database.php';
$db = (new SGEI\Config\Database())->connect();

if (isset($_POST['nombre_materia'])) {
    $sql = "INSERT INTO materias (nombre_materia, creditos) VALUES (?, ?)";
    $db->prepare($sql)->execute([$_POST['nombre_materia'], $_POST['creditos']]);
    header("Location: gestion_academica.php");
}
?>