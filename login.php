<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();
require_once 'db/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = $_POST['username'];
    $password = $_POST['password'];

    /*
    // Code for PDO 
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :input OR email = :input");
    $stmt->execute(['input' => $input]);
    $user = $stmt->fetch();
    */

    // Code for mysqli
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $input, $input);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    // Check if user exists and verify hashed password
    if ($user && password_verify($password, $user['password'])) {

        // Login successful — store session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];

        header("Location: dashboard.html");
        exit;

    } else {
        echo "<script>alert('Invalid username/email or password'); window.history.back();</script>";
    }

} else {
    header("Location: login.html");
    exit;
}
