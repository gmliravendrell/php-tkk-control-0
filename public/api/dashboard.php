<?php
require __DIR__ . "/db.php";

header('Content-Type: application/json; charset=UTF-8');

try {
    $sql = "
        SELECT 
            c.id,
            c.name,
            c.status,
            c.close_time,
            COUNT(DISTINCT ch.participant_id) AS passed,
            (SELECT COUNT(*) FROM participants p WHERE p.status='active') - COUNT(DISTINCT ch.participant_id) AS missing,
            (
              SELECT COUNT(*)
              FROM participants p2
              WHERE p2.status='abandoned'
              AND EXISTS (
                SELECT 1 FROM checkins ch2
                WHERE ch2.participant_id = p2.id
                AND ch2.control_id = c.id
              )
            ) AS abandoned
        FROM controls c
        LEFT JOIN checkins ch ON c.id = ch.control_id
        GROUP BY c.id, c.name, c.status, c.close_time
        ORDER BY c.km_point ASC
    ";

    $res = $conn->query($sql); // <-- usar $conn
    if (!$res) {
        throw new Exception($conn->error);
    }

    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
    }

    echo json_encode($rows);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
