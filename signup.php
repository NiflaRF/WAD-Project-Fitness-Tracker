<?php
session_start(); // Start session for future use (e.g., storing messages or login sessions)

// Database connection using OOP
class Database {
    private $host = "localhost";
    private $db_name = "fitness_tracker"; 
    private $username = "root";
    private $password = "";
    public $conn;

    // Constructor to create the connection
    public function __construct() {
        try {
            $this->conn = new PDO("mysql:host=".$this->host.";dbname=".$this->db_name,
                                  $this->username, $this->password);
            // Set error mode
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
}

// User class to handle registration
class User {
    private $conn;

    // Constructor receives database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Function to validate and register user
    public function register($data) {
        // Extract form inputs
        $fullname = trim($data['fullname']);
        $email = trim($data['email']);
        $dob = $data['dob'];
        $address = trim($data['address']);
        $phone = trim($data['phone']);
        $issue = trim($data['issue']);
        $description = trim($data['description']);
        $password = $data['password'];

        // ========== VALIDATIONS ==========

        // 1. Full name required
        if (empty($fullname)) {
            return "Full Name is required.";
        }

        // 2. Valid Email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Invalid email format.";
        }

        // 3. DOB required
        if (empty($dob)) {
            return "Date of Birth is required.";
        }

        // 4. Address required
        if (empty($address)) {
            return "Address is required.";
        }

        // 5. Validate Sri Lankan phone number (10 digits, starts with 07)
        if (!preg_match('/^07[0-9]{8}$/', $phone)) {
            return "Phone number must be a valid Sri Lankan number (e.g., 0712345678).";
        }

        // 6. Issue required
        if (empty($issue)) {
            return "Please mention your issue.";
        }

        // 7. Description required
        if (empty($description)) {
            return "Please describe your fitness goal.";
        }

        // 8. Password validation (minimum 8 characters, letters and numbers)
        if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            return "Password must be at least 8 characters long and contain both letters and numbers.";
        }

        // Hash the password securely
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // ========== INSERT INTO DATABASE ==========
        try {
            $stmt = $this->conn->prepare("INSERT INTO users 
                (fullname, email, dob, address, phone, issue, description, password) 
                VALUES 
                (:fullname, :email, :dob, :address, :phone, :issue, :description, :password)");

            $stmt->bindParam(':fullname', $fullname);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':dob', $dob);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':issue', $issue);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':password', $hashedPassword);

            $stmt->execute();

            return "Registration successful!";
        } catch (PDOException $e) {
            // In case email already exists (you should have UNIQUE constraint on email column)
            if ($e->getCode() == 23000) {
                return "Email already registered.";
            }
            return "Error: " . $e->getMessage();
        }
    }
}

// ========== PROCESSING THE FORM ==========

// Only run if form submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database(); // Create DB connection
    $user = new User($db->conn); // Pass connection to User class

    $result = $user->register($_POST); // Call register function

    // Show success or error message (this can be passed to a frontend page using session or redirect)
    echo "<script>alert('". $result ."'); window.location.href='../signup.html';</script>";
} else {
    // Redirect if accessed directly
    header("Location: ../signup.html");
    exit();
}
?>
