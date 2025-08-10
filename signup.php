<?php

session_start();

// Database connection 
class Database
{
    private $host = "localhost";
    private $db_name = "fitness_tracker";
    private $username = "root";
    private $password = "";
    public $conn;

    public function __construct()
    {
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name}",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
}

class User
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function register($data)
    {
        // Trim and extract input data
        $full_name = trim($data['fullname']);
        $age = isset($data['age']) ? (int)$data['age'] : null;
        $dob = trim($data['dob']);
        $gender = trim($data['gender']);
        $username = trim($data['uname']);
        $email = trim($data['email']);
        $phone = trim($data['phone']);
        $address = trim($data['address']);
        $issue = trim($data['issue']);
        $goal = trim($data['description']);
        $password = $data['password'];

        // input  validation
        if (empty($full_name)) return "Full name is required.";
        if (empty($dob)) return "Date of birth is required.";
        if (!in_array($gender, ['male', 'female'])) return "Invalid gender.";
        if (empty($username)) return "Username is required.";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return "Invalid email format.";
        if (!preg_match('/^\+?[0-9\s\-]{7,15}$/', $phone)) return "Invalid phone number.";
        if (empty($address)) return "Home address is required.";
        if (empty($issue)) return "Health issue is required.";
        if (empty($goal)) return "Main goal is required.";
        if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            return "Password must be at least 8 characters and include both letters and numbers.";
        }

        // Password hashing
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insert data
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO users 
                    (full_name, age, date_of_birth, gender, username, password, email, phone_number, home_address, health_issue, main_goal) 
                VALUES 
                    (:full_name, :age, :dob, :gender, :username, :hashedpassword, :email, :phone, :address, :issue, :goal)
            ");

            // Bind parameters
            $stmt->bindParam(':full_name', $full_name);
            $stmt->bindParam(':age', $age);
            $stmt->bindParam(':dob', $dob);
            $stmt->bindParam(':gender', $gender);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':hashedpassword', $hashedPassword);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':issue', $issue);
            $stmt->bindParam(':goal', $goal);

            $stmt->execute();

            return "success";
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                return "Username or Email already exists.";
            }
            return "Database error: " . $e->getMessage();
        }
    }
}

// handle the form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();
    $user = new User($db->conn);

    $result = $user->register($_POST);

    // Success: alert and redirect to login page
    if ($result === "success") {
        echo "<script>
           alert('Registration Successful!');
           window.location.href='/WAD-Project-Fitness-Tracker/login.html';
          </script>";
    }

    // Failure: alert and redirect back to signup page
    else {
        echo "<script>
        alert('" . $result . "');
        window.location.href='/WAD-Project-Fitness-Tracker/signup.html';
        </script>";
    }
} else {
    header("Location:/WAD-Project-Fitness-Tracker/signup.html");
    exit();
}

