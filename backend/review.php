<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();
require_once 'db/connect.php';

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$rating = $_POST['rating'] ?? '';
$service = $_POST['service'] ?? '';
$review = $_POST['review'] ?? '';

if (!$name || !$email || !$rating || !$service || !$review) {
  echo json_encode(["status" => "error", "message" => "Please fill all fields."]);
  exit;
}

$stmt = $conn->prepare("INSERT INTO reviews (name, email, rating, service, review) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssiss", $name, $email, $rating, $service, $review);

if ($stmt->execute()) {
  echo json_encode(["status" => "success", "message" => "Thank you for your review!"]);
} else {
  echo json_encode(["status" => "error", "message" => "Could not save review."]);
}

$stmt->close();
$conn->close();
?>
