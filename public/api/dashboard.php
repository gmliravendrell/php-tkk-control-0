<?php
require "db.php";

$sql = "
SELECT p.id, p.dorsal, p.name, p.status,
       GROUP_CONCAT(c.name ORDER BY c.km SEPARATOR ', ') AS passed_controls
FROM participants p
LEFT JOIN checkins ck ON p.id = ck.participant_id
LEFT JOIN controls c ON ck.control_id = c.id
GROUP BY p.id
ORDER BY p.dorsal ASC;
";

$res = $conn->query($sql);
$data = [];
while($row = $res->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>
