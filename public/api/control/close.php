<?php
require __DIR__ . "/../db.php";
header('Content-Type: application/json; charset=UTF-8');

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'msg'=>'Falta id del control']);
    exit;
}

$id = (int)$_GET['id'];

try {
    // Contar pendientes
    $stmt = $conn->prepare("
        SELECT COUNT(*) as missing
        FROM participants p
        WHERE p.status='active' AND NOT EXISTS (
            SELECT 1 FROM checkins ch WHERE ch.participant_id=p.id AND ch.control_id=?
        )
    ");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $missing = (int)$stmt->get_result()->fetch_assoc()['missing'];

    if ($missing > 0) {
        throw new Exception("No se puede cerrar: quedan participantes por pasar");
    }

    // cerrar control
    $stmt = $conn->prepare("UPDATE controls SET status='cerrado', close_time=NOW() WHERE id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();

    echo json_encode(['ok'=>true]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]);
}
