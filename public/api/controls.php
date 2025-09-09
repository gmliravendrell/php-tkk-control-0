<?php
require __DIR__ . "/db.php";
header('Content-Type: application/json; charset=UTF-8');

try {
    $res = $conn->query("SELECT id, name, km_point FROM controls ORDER BY km_point ASC");
    $controls = [];
    while ($row = $res->fetch_assoc()) {
        $controls[] = $row;
    }
    echo json_encode($controls);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error'=>$e->getMessage()]);
}