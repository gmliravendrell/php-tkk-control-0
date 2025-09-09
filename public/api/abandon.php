<?php
require __DIR__ . "/db.php";
header('Content-Type: application/json; charset=UTF-8');

$dorsal = $_POST['dorsal'] ?? null;
$control_id = (int)($_POST['control_id'] ?? 0);

if (!$dorsal || !$control_id) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'msg'=>'Falta dorsal o control']);
    exit;
}

try {
    // buscar participante
    $stmt = $conn->prepare("SELECT id,status FROM participants WHERE dorsal=?");
    $stmt->bind_param("i",$dorsal);
    $stmt->execute();
    $p = $stmt->get_result()->fetch_assoc();
    if (!$p) throw new Exception("Participante no encontrado");
    if ($p['status'] === 'abandoned') throw new Exception("Participante ya abandonado");

    // marcar como abandonado
    $stmt = $conn->prepare("UPDATE participants SET status='abandoned' WHERE id=?");
    $stmt->bind_param("i",$p['id']);
    $stmt->execute();

    // opcional: crear checkin de control para registro
    $stmt = $conn->prepare("INSERT INTO checkins(participant_id, control_id) VALUES(?,?)");
    $stmt->bind_param("ii",$p['id'],$control_id);
    $stmt->execute();

    echo json_encode(['ok'=>true]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]);
}
