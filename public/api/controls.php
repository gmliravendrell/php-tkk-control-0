<?php
require "db.php";

$sql = "SELECT id, name, km_point FROM controls ORDER BY km_point ASC";
$res = $conn->query($sql);

$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>