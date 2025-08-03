
<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();
require_once 'db/connect.php';

$sql = "SELECT name, rating, service, review, created_at FROM reviews ORDER BY created_at DESC LIMIT 10";
$result = $conn->query($sql);

$reviews = [];

while ($row = $result->fetch_assoc()) {
  $reviews[] = $row;
}

header('Content-Type: application/json');
echo json_encode($reviews);

$conn->close();
?>
