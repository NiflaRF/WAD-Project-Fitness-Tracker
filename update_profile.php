<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

require_once 'db/connect.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid input"]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Prepare the SQL statement
$query = "UPDATE users 
          SET full_name = ?, age = ?, 
              phone_number = ?, home_address = ?, main_goal = ? 
          WHERE user_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param(
    "sisssi",
    $data['full_name'],
    $data['age'], 
    $data['phone_number'],
    $data['home_address'],
    $data['main_goal'],
    $user_id
);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Profile updated successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Database update failed"]);
}

$stmt->close();
$conn->close();
?>
