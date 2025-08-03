<?php
$host = "localhost";
$db_user = "root";
$db_password = ""; 
$db_name = "fitness_tracker";

$conn = new mysqli($host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
   
    echo json_encode(["status" => "error", "message" => "Database connection failed."]);
    exit;
}
