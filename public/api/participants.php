<?php
require "db.php";

$sql = "SELECT id, name, dorsal, status FROM participants ORDER BY dorsal ASC";
$res = $conn->query($sql);

$data = [];
while($row = $res->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>
