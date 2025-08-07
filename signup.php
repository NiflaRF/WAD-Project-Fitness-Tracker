<?php
session_start();

// Database connection using PDO OOP
class Database {
    private $host = "localhost";
    private $db_name = "fitness_db"; // update to your DB name
    private $username = "root";
    private $password = "";
    public $conn;

    public function __construct() {
        try {
            $this->conn = new PDO("mysql:host={$this->host};dbname={$this->db_name}",
                                  $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("DB Connection failed: " . $e->getMessage());
        }
    }
}

class User {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register($data) {
        // Trim and extract input data
        $full_name = trim($data['full_name']);
        $age = isset($data['age']) ? (int)$data['age'] : null;
        $dob = trim($data['date_of_birth']);
        $gender = trim($data['gender']);
        $username = trim($data['username']);
        $email = trim($data['email']);
        $phone = trim($data['phone_number']);
        $address = trim($data['home_address']);
        $issue = trim($data['health_issue']);
        $goal = trim($data['main_goal']);
        $password = $data['password'];

        // ========== VALIDATION ==========
        if (empty($full_name)) return "Full name is required.";
        if (empty($dob)) return "Date of birth is required.";
        if (!in_array($gender, ['Male', 'Female'])) return "Invalid gender.";
        if (empty($username)) return "Username is required.";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return "Invalid email format.";
        if (!preg_match('/^07[0-9]{8}$/', $phone)) return "Invalid Sri Lankan phone number.";
        if (empty($address)) return "Home address is required.";
        if (empty($issue)) return "Health issue is required.";
        if (empty($goal)) return "Main goal is required.";
        if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            return "Password must be at least 8 characters and include both letters and numbers.";
        }

        // Password hashing
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // ========== INSERT QUERY ==========
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO users 
                    (full_name, age, date_of_birth, gender, username, password, email, phone_number, home_address, health_issue, main_goal) 
                VALUES 
                    (:full_name, :age, :dob, :gender, :username, :password, :email, :phone, :address, :issue, :goal)
            ");

            // Bind parameters
            $stmt->bindParam(':full_name', $full_name);
            $stmt->bindParam(':age', $age);
            $stmt->bindParam(':dob', $dob);
            $stmt->bindParam(':gender', $gender);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':issue', $issue);
            $stmt->bindParam(':goal', $goal);

            $stmt->execute();

            return "Registration successful!";
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                return "Username or Email already exists.";
            }
            return "Database error: " . $e->getMessage();
        }
    }
}

// Handle the form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();
    $user = new User($db->conn);

    $result = $user->register($_POST);

    echo "<script>alert('". $result ."'); window.location.href='../signup.html';</script>";
} else {
    header("Location: ../signup.html");
    exit();
}
?>
