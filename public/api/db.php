<?php
$host = "db";
$user = "tkkuser";   // cámbialo según tu config
$pass = "tkkpass";
$db   = "tkk";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error DB: " . $conn->connect_error);
}

header("Content-Type: application/json; charset=UTF-8");
?>
