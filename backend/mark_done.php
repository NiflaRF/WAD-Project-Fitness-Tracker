<?php
session_start();
require_once 'db/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id']; // ✅ Make sure this is set after login
    $workout_id = intval($_POST['workout_id']);

    $stmt = $conn->prepare("INSERT INTO user_workouts (user_id, workout_id, completed_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("ii", $user_id, $workout_id);

    if ($stmt->execute()) {
        echo "Workout marked as done!";
    } else {
        http_response_code(500);
        echo "Failed to save workout.";
    }

    $stmt->close();
    $conn->close();
}
