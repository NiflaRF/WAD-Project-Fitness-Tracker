<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(["error" => "Unauthorized"]);
  exit;
}

require_once 'db/connect.php';

$user_id = $_SESSION['user_id'];

$query = "SELECT full_name, age, date_of_birth, gender, email, phone_number, home_address, main_goal FROM users WHERE user_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  http_response_code(404);
  echo json_encode(["error" => "User not found"]);
  exit;
}

$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($user);
?>