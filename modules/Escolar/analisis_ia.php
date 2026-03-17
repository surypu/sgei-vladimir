<?php
// modules/escolar/analisis_ia.php
// Lógica de "IA" para detectar alumnos en riesgo basándose en promedios
function predecirRiesgo(float $promedio): string {
    return match(true) {
        $promedio < 7.0 => "ALTA PROBABILIDAD DE REPROBACIÓN. Se recomienda tutoría inmediata.",
        $promedio < 8.5 => "Rendimiento estable. Mantener monitoreo mensual.",
        default          => "Rendimiento óptimo. Candidato a beca de excelencia."
    };
}

// Aquí iría la integración con Gemini API si quisieras automatizar avisos