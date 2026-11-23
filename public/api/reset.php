<?php
/**
 * Reinicio para reintentar:
 *   - lab_id=XX-00 → vuelve el laboratorio a su estado inicial (saldo, carrito…).
 *                    No borra el progreso ni las pistas usadas.
 *   - scope=all    → borra TODO el progreso del alumno (desde el portal).
 */
require_once 'bootstrap.php';

if (!is_post()) {
    json_out(['ok' => false, 'message' => 'Usa POST.'], 405);
}
$student = require_student(true);
$sid     = (int) $student['id'];

if (($_POST['scope'] ?? '') === 'all') {
    reset_student($sid);
    redirect('/');
}

$labId = (string) ($_POST['lab_id'] ?? '');
if (!isset(labs()[$labId])) {
    json_out(['ok' => false, 'message' => 'Laboratorio desconocido.'], 404);
}
state_reset($sid, $labId);

// Estos labs marcan su resolución en la sesión: también se limpia para reintentar.
$sessionKeys = [
    'CF-01' => 'cf01_ok',
    'CF-02' => 'cf02_ok',
    'CF-03' => 'cf03_ok',
    'CF-04' => 'cf04_ok',
    'CF-05' => 'cf05_ok',
];
if (isset($sessionKeys[$labId])) {
    unset($_SESSION[$sessionKeys[$labId]]);
}
json_out(['ok' => true, 'message' => 'Laboratorio reiniciado.']);
