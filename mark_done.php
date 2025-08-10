<?php
session_start();
require_once 'db/connect.php';

if (!isset($_SESSION['user_id'])) {
    echo "Error: You must be logged in.";
    exit;
}

$user_id = $_SESSION['user_id'];

// Get workout_id from POST request
if (!isset($_POST['workout_id']) || empty($_POST['workout_id'])) {
    echo "Error: Workout ID is required.";
    exit;
}

$workout_id = intval($_POST['workout_id']);

// Insert record into user_workouts
$sql = "INSERT INTO user_workouts (user_id, workout_id, completed_at) 
        VALUES (?, ?, NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $workout_id);

if ($stmt->execute()) {
    echo "Workout marked as done!";
} else {
    echo "Error: " . $conn->error;
}

$stmt->close();
$conn->close();
