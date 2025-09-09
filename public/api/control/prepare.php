<?php
require __DIR__ . "/db.php";
header('Content-Type: application/json; charset=UTF-8');

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error'=>'Falta id del control']);
    exit;
}

$id = (int)$_GET['id'];

try {
    // Datos básicos del control
    $stmt = $conn->prepare("SELECT * FROM controls WHERE id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $control = $stmt->get_result()->fetch_assoc();
    if (!$control) throw new Exception("Control no encontrado");

    // Contadores
    // Pasados
    $stmt = $conn->prepare("SELECT COUNT(*) as passed FROM checkins WHERE control_id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $passed = $stmt->get_result()->fetch_assoc()['passed'];

    // Faltan (active sin marcar en este control)
    $stmt = $conn->prepare("
        SELECT COUNT(*) as missing 
        FROM participants p
        WHERE p.status='active' AND NOT EXISTS (
            SELECT 1 FROM checkins ch WHERE ch.participant_id=p.id AND ch.control_id=?
        )
    ");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $missing = $stmt->get_result()->fetch_assoc()['missing'];

    // Abandonos en este control
    $stmt = $conn->prepare("
        SELECT COUNT(*) as abandoned
        FROM participants p
        WHERE p.status='abandoned'
        AND EXISTS (
            SELECT 1 FROM checkins ch WHERE ch.participant_id=p.id AND ch.control_id=?
        )
    ");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $abandoned = $stmt->get_result()->fetch_assoc()['abandoned'];

    // Combinar y devolver
    $control['passed'] = (int)$passed;
    $control['missing'] = (int)$missing;
    $control['abandoned'] = (int)$abandoned;

    echo json_encode($control);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error'=>$e->getMessage()]);
}
