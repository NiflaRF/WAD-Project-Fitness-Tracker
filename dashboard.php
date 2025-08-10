<?php
// dashboard.php
// Return JSON: user data + completed workouts (title, description, completed_at)

// IMPORTANT: disable PHP error HTML output so JSON parsing doesn't break.
// During development you can temporarily enable errors but avoid sending HTML before the JSON response.
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// Start session and check authentication
session_start();
if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

require_once 'db/connect.php'; // must set $conn = new mysqli(...);

// Validate DB connection (basic)
if (!isset($conn) || !($conn instanceof mysqli)) {
  http_response_code(500);
  echo json_encode(['error' => 'Database connection not found']);
  exit;
}

$user_id = (int) $_SESSION['user_id'];

// Fetch user info
$user = null;
$sqlUser = "SELECT full_name, age, date_of_birth, gender, email, phone_number, home_address, main_goal
            FROM users
            WHERE user_id = ?";
if ($stmt = $conn->prepare($sqlUser)) {
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $res = $stmt->get_result();
  if ($res->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
    exit;
  }
  $user = $res->fetch_assoc();
  $stmt->close();
} else {
  http_response_code(500);
  echo json_encode(['error' => 'Failed to prepare user query']);
  exit;
}

// Fetch completed workouts for this user
$workouts = [];
$sqlWorkouts = "SELECT uw.id AS user_workout_id, uw.workout_id, w.title, w.description, uw.completed_at
                FROM user_workouts uw
                JOIN workouts w ON uw.workout_id = w.workout_id
                WHERE uw.user_id = ?
                ORDER BY uw.completed_at DESC
                LIMIT 50";
if ($stmt = $conn->prepare($sqlWorkouts)) {
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($row = $res->fetch_assoc()) {
    // Format completed_at as readable string (Y-m-d H:i:s). If null, keep null.
    $completed_at = $row['completed_at'] ? (new DateTime($row['completed_at']))->format('Y-m-d H:i:s') : null;

    $workouts[] = [
      'user_workout_id' => (int)$row['user_workout_id'],
      'workout_id' => (int)$row['workout_id'],
      'title' => $row['title'],
      'description' => $row['description'],
      'completed_at' => $completed_at
    ];
  }
  $stmt->close();
} else {
  http_response_code(500);
  echo json_encode(['error' => 'Failed to prepare workouts query']);
  exit;
}

$conn->close();

// Merge and send JSON
$user['workouts'] = $workouts;
echo json_encode($user, JSON_UNESCAPED_UNICODE);
exit;
