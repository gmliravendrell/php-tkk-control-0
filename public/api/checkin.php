<?php
require "db.php";

$participant_id = $_POST["participant_id"] ?? null;
$control_id     = $_POST["control_id"] ?? null;

if (!$participant_id || !$control_id) {
    echo json_encode(["ok" => false, "msg" => "Datos incompletos"]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO checkins (participant_id, control_id) VALUES (?, ?)");
$stmt->bind_param("ii", $participant_id, $control_id);

if ($stmt->execute()) {
    echo json_encode(["ok" => true]);
} else {
    echo json_encode(["ok" => false, "msg" => $conn->error]);
}
?>
