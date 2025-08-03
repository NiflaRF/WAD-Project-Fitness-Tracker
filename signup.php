<?php

// Database configuration
$host = 'localhost';
$db   = 'royal_fitness';
$user = 'root';
$pass = ''; //MySQL password
$charset = 'utf8mb4';

// Connect using MySQLi
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Validate and sanitize input
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Trim and remove special characters
    $issue = htmlspecialchars(trim($_POST['issue']));
    $goal  = htmlspecialchars(trim($_POST['goal']));

    // Basic validation
    if (!empty($issue) && !empty($goal)) {
        // Use prepared statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO user_goals (issue, goal) VALUES (?, ?)");
        if ($stmt) {
            $stmt->bind_param("ss", $issue, $goal);  
            if ($stmt->execute()) {
                // Redirect or show success
                header("Location: home.html");
                exit();
            } else {
                echo "Error executing statement: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    } else {
        echo "All fields are required!";
    }
}

$conn->close();
?>
