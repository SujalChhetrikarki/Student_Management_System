<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

include '../Database/db_connect.php';

// Only accept POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: add_student.php");
    exit;
}

// Collect and sanitize form data
$student_id    = trim($_POST['student_id']);
$name          = trim($_POST['name']);
$email         = trim($_POST['email']);
$password_raw  = $_POST['password'];
$class_id      = intval($_POST['class_id']);
$date_of_birth = $_POST['date_of_birth'];
$gender        = $_POST['gender'];

// Validate required fields
if (empty($student_id) || empty($name) || empty($email) || empty($password_raw) || empty($class_id) || empty($date_of_birth) || empty($gender)) {
    $_SESSION['error'] = "⚠ All fields are required!";
    header("Location: add_student.php");
    exit;
}

// Validate gender
$allowed_genders = ['Male', 'Female', 'Other'];
if (!in_array($gender, $allowed_genders)) {
    $_SESSION['error'] = "⚠ Invalid gender selected!";
    header("Location: add_student.php");
    exit;
}

// Check for duplicate student_id or email
$check = $conn->prepare("SELECT * FROM students WHERE student_id = ? OR email = ?");
$check->bind_param("ss", $student_id, $email);
$check->execute();
$result = $check->get_result();
if ($result->num_rows > 0) {
    $_SESSION['error'] = "⚠ Student ID or Email already exists!";
    $check->close();
    header("Location: add_student.php");
    exit;
}
$check->close();

// Hash the password securely
$password = password_hash($password_raw, PASSWORD_DEFAULT);

$sql = "INSERT INTO students (student_id, name, email, password, class_id, date_of_birth, gender) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt) {
    // Corrected bind_param types
    $stmt->bind_param("ssssiss", $student_id, $name, $email, $password, $class_id, $date_of_birth, $gender);
    if ($stmt->execute()) {
        $_SESSION['success'] = "✅ Student added successfully!";
        header("Location: add_student.php");
        exit;
    } else {
        $_SESSION['error'] = "❌ Database error: " . $stmt->error;
        header("Location: add_student.php");
        exit;
    }
    $stmt->close();
} else {
    $_SESSION['error'] = "❌ Error preparing query: " . $conn->error;
    header("Location: add_student.php");
    exit;
}
$conn->close();
?>