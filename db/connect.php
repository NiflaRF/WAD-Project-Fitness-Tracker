<?php
$host = "localhost";
$db_user = "root";
$db_password = "root"; // Update if your MySQL has a password
$db_name = "fitness_tracker"; //your database name

// Create connection
$conn = new mysqli($host, $db_user, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
